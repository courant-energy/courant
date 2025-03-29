<?php

namespace App\Filament\Resources\ProfileResource\Widgets;

use App\Models\Profile;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget as BaseWidget;
use App\Models\Socket;
use Filament\Support\Enums\FontWeight;
use Filament\Tables\Columns\TextColumn\TextColumnSize;

class SocketTable extends BaseWidget
{

    public Profile $record;

    protected int|string|array $columnSpan = 'full';

    public function table(Table $table): Table
    {
        return $table
            ->heading('Sockets Using This Profile')
            ->description('List of sockets currently associated with this profile')
            ->emptyStateHeading('No sockets found')
            ->emptyStateDescription('No sockets are currently using this profile.')
            ->emptyStateIcon('heroicon-o-light-bulb')
            ->emptyStateActions([
                Tables\Actions\Action::make('create_socket')
                    ->label('Manage Sockets')
                    ->url(route('filament.app.resources.sockets.index')),
            ])
            ->query(
                function () {
                    // dd($record->id);
                    return Socket::query()
                        ->where('profile_id', $this->record->id)
                        ->with(['iotDevice']);
                }
            )
            ->paginated(false)
            ->contentGrid([
                'default' => 2
            ])
            ->columns([
                Tables\Columns\Layout\Stack::make([
                    Tables\Columns\TextColumn::make('iotDevice.name')
                        ->label('IoT Device Name')
                        ->weight(FontWeight::Bold)
                        ->searchable(),
                    Tables\Columns\TextColumn::make('iotDevice.device_uuid')
                        ->label('Device UUID')
                        ->color('gray')
                        ->size(TextColumnSize::ExtraSmall)
                        ->weight(FontWeight::ExtraLight)
                        ->searchable(),
                    Tables\Columns\TextColumn::make('name')
                        ->label('Socket Name')
                        ->searchable(),
                ])
            ]);
    }
}
