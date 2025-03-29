<?php

namespace App\Filament\Resources\IotDeviceResource\Pages;

use App\Filament\Resources\IotDeviceResource;
use App\Models\IotDevice;
use App\Services\IotDeviceService;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Forms\Form;
use Filament\Forms\Components;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\Page;

class RegisterIotDevice extends Page implements HasForms
{

    use InteractsWithForms;

    public ?array $data = [];

    protected static string $resource = IotDeviceResource::class;

    protected static ?string $title = 'Register an IoT Device';

    protected static string $view = 'filament.resources.iot-device-resource.pages.register-iot-device';

    protected IotDeviceService $iotDeviceService;

    public function mount()
    {
        $this->data = [
            'name' => '',
            'device_uuid' => '',
            'password' => '',
        ];
    }

    public function form(Form $form): Form
    {
        return $form

            ->schema([
                Components\Section::make('IoT Device Information')
                    ->schema([
                        Components\TextInput::make('name')
                            ->helperText('An alias for the IoT device. This name will be displayed in the dashboard.'),
                        Components\TextInput::make('device_uuid')
                            ->helperText('The UUID is a unique identifier for the IoT device. It can be found on the device itself.')
                            ->label('UUID'),
                        Components\TextInput::make('password')
                            ->helperText('The password is used to authenticate the IoT device when it connects to the server. For first-time setup, you can find the password on the device itself.')
                            ->revealable()
                            ->password(),
                    ])
                    ->aside()
                    ->description('To link an IoT device to your account, you need to provide the following information.'),
            ])
            ->statePath('data');
    }

    public function register(IotDeviceService $iotDeviceService): void
    {
        $this->validate(
            [
                'data.name' => 'required|string',
                'data.device_uuid' => 'required|string',
                'data.password' => 'required|string',
            ],
            [
                'data.device_uuid.required' => 'The UUID field is required.',
            ]
        );

        $device = IotDevice::where('is_active', false)
            ->where('device_uuid', $this->data['device_uuid'])
            ->first();

        // Check if the device exists
        if (!$device) {
            \Log::error('Device not found', ['device_uuid' => $this->data['device_uuid'], 'user_id' => auth()->id()]);
            Notification::make()
                ->danger()
                ->title('Registration Failed')
                ->body('Device not found. The device with the UUID provided was not found. Please check the UUID and try again.')
                ->send();
            return;
        }

        // Check if the password is correct
        if ($device->password !== $this->data['password']) {
            \Log::error('Incorrect password', ['device_uuid' => $this->data['device_uuid'], 'user_id' => auth()->id()]);
            Notification::make()
                ->danger()
                ->title('Registration Failed')
                ->body('Incorrect password. The password provided does not match the password for the device. Please check the password and try again.')
                ->send();
            return;
        }


        try {
            // Update the device
            $iotDeviceService->linkDeviceToUser($device->device_uuid, auth()->id());
            $device->name = $this->data['name'];
            $device->save();
            // Log the registration
            \Log::info('Device linked', ['device_uuid' => $this->data['device_uuid'], 'user_id' => auth()->id()]);
        } catch (\Exception $e) {
            \Log::error('Failed to register device', [
                'device_uuid' => $this->data['device_uuid'],
                'user_id' => auth()->id(),
                'error' => $e->getMessage()
            ]);

            Notification::make()
                ->danger()
                ->title('Registration Failed')
                ->body('An error occurred while registering the device. Please try again later.')
                ->send();

            return;
        }
        
        // Notify the user
        \Log::info('Device registered', ['device_uuid' => $this->data['device_uuid'], 'user_id' => auth()->id()]);
        Notification::make()
            ->success()
            ->title('Registration Successful')
            ->body('The device has been successfully registered.')
            ->send();
    
        // TODO: Add an email notification to the user

        $this->redirect(route('filament.app.resources.iot-devices.index'));
    }
}
