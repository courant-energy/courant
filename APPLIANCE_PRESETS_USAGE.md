# Appliance Presets Implementation

This implementation adds an appliance presets dropdown to the Profile creation form that automatically fills in power values for common appliances.

## Features

1. **JSON-based presets**: Common appliances and their power characteristics are stored in `resources/json/appliances.json`
2. **Auto-fill functionality**: When a user selects an appliance preset, the form fields are automatically populated
3. **Custom profiles**: Users can still choose "Custom Profile" to enter their own values
4. **Edit form optimization**: The preset dropdown is hidden on edit forms to avoid confusion

## How it works

### 1. JSON Structure
```json
{
  "appliances": [
    {
      "name": "Refrigerator",
      "expected_power": 150,
      "max_power_threshold": 200,
      "min_power_threshold": 80
    }
  ]
}
```

### 2. Form Implementation
- Uses Filament's `Select` component with `live()` and `afterStateUpdated()` callbacks
- The dropdown options are generated from the JSON file
- When an appliance is selected, the `afterStateUpdated` callback uses `Set` to populate the form fields

### 3. Model Helper Method
```php
public static function getAppliancePresets()
{
    $jsonPath = resource_path('json/appliances.json');
    if (!file_exists($jsonPath)) {
        return [];
    }
    
    $appliances = json_decode(file_get_contents($jsonPath), true);
    return $appliances['appliances'] ?? [];
}
```

## Available Appliances

The preset includes 25 common household appliances:
- Refrigerator (150W)
- Washing Machine (500W)  
- Microwave Oven (1000W)
- Dishwasher (1800W)
- Air Conditioners (1200-2000W)
- Kitchen appliances (Coffee Machine, Kettle, Toaster, etc.)
- Electronics (TV, Computer, Gaming Console)
- Home appliances (Hair Dryer, Iron, Vacuum Cleaner)
- Lighting (LED, Fluorescent)
- And more...

## Usage in Forms

When creating a new Profile:
1. User sees "Appliance Preset" dropdown at the top of the form
2. User can select from common appliances or choose "Custom Profile"
3. If an appliance is selected, the name and power fields are automatically filled
4. User can still modify the auto-filled values if needed
5. The form saves normally - the preset selection is not stored in the database

## Testing

Tests are included to verify:
- Appliance presets can be loaded from JSON
- Required fields are present for each appliance
- Power values are reasonable (min ≤ expected ≤ max)
- Common appliances are included in the presets

## Extending

To add new appliances:
1. Edit `resources/json/appliances.json`
2. Add new appliance objects with required fields:
   - `name`: Display name
   - `expected_power`: Normal operating power in watts
   - `max_power_threshold`: Maximum acceptable power
   - `min_power_threshold`: Minimum acceptable power

The form will automatically pick up new appliances without code changes.
