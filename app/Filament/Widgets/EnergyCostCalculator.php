<?php

namespace App\Filament\Widgets;

use Filament\Widgets\Widget;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Get;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Forms\Form;

class EnergyCostCalculator extends Widget implements HasForms
{
    use InteractsWithForms;

    protected static string $view = 'filament.widgets.energy-cost-calculator';
    
    protected static ?string $heading = 'Cost Calculator';
    
    protected int|string|array $columnSpan = 1;
    
    protected static ?int $sort = 3;
    
    // Form data
    public float $powerConsumption = 0.0;
    public float $rate = 0.0;
    public float $calculatedCost = 0;
    
    public function mount(): void
    {
        $this->form->fill([
            'powerConsumption' => $this->powerConsumption,
            'rate' => $this->rate,
            'calculatedCost' => $this->calculatedCost,
        ]);
    }
    
    public function form(Form $form): Form
    {
        return $form
            ->schema([
                TextInput::make('powerConsumption')
                    ->label('Power Consumption (kWh)')
                    ->numeric()
                    ->minValue(0)
                    ->required()
                    ->live(true)
                    ->afterStateUpdated(function (Get $get) {
                        $this->calculateCost($get('powerConsumption'), $get('rate'));
                    }),
                    
                TextInput::make('rate')
                    ->label('Rate (PHP per kWh)')
                    ->numeric()
                    ->minValue(0)
                    ->required()
                    ->live(true)
                    ->afterStateUpdated(function (Get $get) {
                        $this->calculateCost($get('powerConsumption'), $get('rate'));
                    }),
            ]);
    }
    
    public function calculateCost($powerConsumption, $rate): void
    {
        if (is_numeric($powerConsumption) && is_numeric($rate)) {
            $this->calculatedCost = floatval($powerConsumption) * floatval($rate) * 30; // Assuming 30 days in a month
        } else {
            $this->calculatedCost = 0;
        }
    }
}
