<?php

namespace App\Filament\Pages;

use Filament\Actions\Action;
use Filament\Forms\Components\Placeholder;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\TextInput;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Filament\Support\Enums\MaxWidth;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\ValidationException;

class DataPrivacy extends Page
{
    protected static ?string $navigationIcon = 'heroicon-o-shield-check';

    protected static ?string $navigationGroup = 'Data & Insights';

    protected static string $view = 'filament.pages.data-privacy';

    protected static ?string $title = 'Data Privacy & Account Management';

    protected static ?string $navigationLabel = 'Data Privacy';

    public function deleteAccountAction(): Action
    {
        return
            Action::make('deleteAccount')
                ->label('Delete My Account')
                ->color('danger')
                ->icon('heroicon-o-trash')
                ->requiresConfirmation()
                ->modalHeading('Delete Account & Wipe All Data')
                ->modalDescription('This action will permanently delete your account and ALL associated data including IoT devices, readings, profiles, questions, and files. This cannot be undone.')
                ->modalSubmitActionLabel('Delete My Account & All Data')
                ->modalCancelActionLabel('Cancel')
                ->modalWidth(MaxWidth::TwoExtraLarge)
                ->form([
                    Section::make('Account Deletion Confirmation')
                        ->description('Please carefully read and confirm the following before proceeding with account deletion.')
                        ->schema([
                            Placeholder::make('warning')
                                ->content(new \Illuminate\Support\HtmlString('
                                    <div class="p-4 bg-red-50 border-l-4 border-red-400 rounded-md">
                                        <div class="flex">
                                            
                                            <div class="ml-3">
                                                <h3 class="text-lg font-medium text-red-800 mb-2">⚠️ DANGER ZONE ⚠️</h3>
                                                <div class="text-sm text-red-700">
                                                    <p class="mb-3 font-semibold">This action will permanently delete:</p>
                                                    <ul class="list-disc list-inside space-y-1 mb-4">
                                                        <li>Your user account and profile</li>
                                                        <li>All IoT devices and their configurations</li>
                                                        <li>All energy readings and historical data</li>
                                                        <li>All profiles and monitoring settings</li>
                                                        <li>All support tickets and questions</li>
                                                        <li>All notifications and alerts</li>
                                                        <li>All uploaded files and images</li>
                                                        <li>All system logs related to your account</li>
                                                    </ul>
                                                    <p class="font-bold text-red-800">This action cannot be undone and all data will be lost forever.</p>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                '))
                                ->columnSpanFull(),

                            TextInput::make('email_confirmation')
                                ->label('Type your email address to confirm')
                                ->helperText('Please type your email address exactly as shown: '.Auth::user()->email)
                                ->required()
                                ->rules([
                                    function () {
                                        return function (string $attribute, $value, \Closure $fail) {
                                            if ($value !== Auth::user()->email) {
                                                $fail('The email address does not match your account email.');
                                            }
                                        };
                                    },
                                ])
                                ->columnSpanFull(),

                            TextInput::make('confirmation_text')
                                ->label('Type "DELETE MY ACCOUNT" to confirm')
                                ->helperText('Please type exactly: DELETE MY ACCOUNT')
                                ->required()
                                ->rules([
                                    function () {
                                        return function (string $attribute, $value, \Closure $fail) {
                                            if ($value !== 'DELETE MY ACCOUNT') {
                                                $fail('Please type exactly "DELETE MY ACCOUNT" to confirm.');
                                            }
                                        };
                                    },
                                ])
                                ->columnSpanFull(),

                            TextInput::make('password')
                                ->label('Enter your password')
                                ->password()
                                ->required()
                                ->rules([
                                    function () {
                                        return function (string $attribute, $value, \Closure $fail) {
                                            if (! Hash::check($value, Auth::user()->password)) {
                                                $fail('The password is incorrect.');
                                            }
                                        };
                                    },
                                ])
                                ->columnSpanFull(),
                        ])
                        ->columns(1),
                ])
                ->action(function (array $data) {
                    try {
                        // Get the current user
                        $user = Auth::user();

                        // Log the account deletion attempt
                        Log::warning('User account deletion initiated', [
                            'user_id' => $user->id,
                            'email' => $user->email,
                            'ip_address' => request()->ip(),
                            'user_agent' => request()->userAgent(),
                            'timestamp' => now(),
                        ]);

                        // Perform the account deletion
                        $user->delete();

                        // Log out the user
                        Auth::logout();

                        // Invalidate the session
                        request()->session()->invalidate();
                        request()->session()->regenerateToken();

                        // Show success notification
                        Notification::make()
                            ->title('Account Deleted Successfully')
                            ->body('Your account and all associated data have been permanently deleted.')
                            ->success()
                            ->persistent()
                            ->send();

                        // Redirect to home page
                        return redirect('/');

                    } catch (\Exception $e) {
                        Log::error('Failed to delete user account', [
                            'user_id' => Auth::id(),
                            'error' => $e->getMessage(),
                            'trace' => $e->getTraceAsString(),
                        ]);

                        Notification::make()
                            ->title('Error Deleting Account')
                            ->body('An error occurred while deleting your account. Please contact support.')
                            ->danger()
                            ->persistent()
                            ->send();

                        throw ValidationException::withMessages([
                            'error' => 'Failed to delete account. Please try again or contact support.',
                        ]);
                    }
                });
    }
}
