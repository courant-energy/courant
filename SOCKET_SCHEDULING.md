# Socket Scheduling Feature

This feature allows users to automatically control socket states based on time schedules configured in device profiles.

## Features

- **Time-based scheduling**: Set turn on/off times for sockets
- **Day-of-week filtering**: Choose which days the schedule should be active
- **Midnight-spanning schedules**: Support for schedules that cross midnight (e.g., 22:00 to 06:00)
- **Profile-based**: Schedule is configured at the profile level and applies to all linked sockets
- **Automatic processing**: Schedules are processed every minute via Laravel scheduler

## How it Works

1. **Profile Configuration**: Users enable scheduling in a profile and set:
   - Turn on time
   - Turn off time  
   - Active days of the week

2. **Socket Linking**: Any socket linked to a profile with scheduling enabled will be controlled automatically

3. **Schedule Processing**: Every minute, the system:
   - Checks all profiles with scheduling enabled
   - Determines if sockets should be on or off based on current time
   - Updates socket states as needed

## Database Changes

New columns added to `profiles` table:
- `scheduling_enabled` (boolean): Whether scheduling is active for this profile
- `turn_on_time` (time): Time to turn sockets on
- `turn_off_time` (time): Time to turn sockets off  
- `schedule_days` (json): Array of active days ['monday', 'tuesday', etc.]

## Usage Examples

### Basic Weekday Schedule
```php
$profile = Profile::create([
    'name' => 'Office Equipment',
    'scheduling_enabled' => true,
    'turn_on_time' => '08:00',
    'turn_off_time' => '18:00',
    'schedule_days' => ['monday', 'tuesday', 'wednesday', 'thursday', 'friday']
]);
```

### Night Schedule (Spanning Midnight)
```php
$profile = Profile::create([
    'name' => 'Security Lighting',
    'scheduling_enabled' => true,
    'turn_on_time' => '22:00',
    'turn_off_time' => '06:00',
    'schedule_days' => ['monday', 'tuesday', 'wednesday', 'thursday', 'friday', 'saturday', 'sunday']
]);
```

## API Methods

### Profile Methods
- `shouldBeOn()`: Check if profile should be on at current time
- `applySchedule()`: Apply schedule to all linked sockets
- `getNextScheduleChange()`: Get info about next scheduled state change

### Socket Methods  
- `isScheduled()`: Check if socket has scheduling enabled
- `getScheduledState()`: Get what state socket should be in according to schedule
- `isInSync()`: Check if current state matches scheduled state
- `getNextScheduleChange()`: Get next schedule change for this socket

## Commands

### Manual Schedule Processing
```bash
# Process schedules immediately
php artisan schedules:process

# Dry run to see what would change
php artisan schedules:process --dry-run
```

## Scheduler Setup

The feature automatically runs every minute via Laravel's task scheduler. Ensure your cron is configured:

```bash
* * * * * cd /path-to-your-project && php artisan schedule:run >> /dev/null 2>&1
```

## User Interface

In the Filament admin panel, users can:

1. **Enable/Disable Scheduling**: Toggle in the profile form
2. **Set Times**: Use time pickers for turn on/off times
3. **Select Days**: Checkboxes for days of the week
4. **Preview Schedule**: See a summary of the configured schedule
5. **View Status**: Table shows current scheduling status for each profile

## Technical Implementation

- **Job**: `ProcessSocketSchedulesJob` handles the actual processing
- **Command**: `ProcessSocketSchedules` provides manual execution and dry-run capability
- **Scheduler**: Configured in `routes/console.php` to run every minute
- **Models**: Enhanced `Profile` and `Socket` models with scheduling methods
- **UI**: Extended Filament resource with scheduling form fields

## Testing

Run the test suite to verify scheduling functionality:

```bash
php artisan test --filter=SocketSchedulingTest
```

## Notes

- Schedules are processed every minute, so state changes occur within 1 minute of the scheduled time
- If no `schedule_days` are specified, the schedule applies to all days
- Sockets without a linked profile or with scheduling disabled are not affected
- The system logs schedule processing activity for debugging
