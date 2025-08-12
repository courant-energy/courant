# Socket Scheduling Feature

This feature allows users to automatically control socket states based on time schedules configured in device profiles. **The schedule only applies at the exact scheduled times, allowing users to manually override socket states between scheduled events.**

## Key Features

- **Time-based scheduling**: Set turn on/off times for sockets
- **Day-of-week filtering**: Choose which days the schedule should be active
- **Midnight-spanning schedules**: Support for schedules that cross midnight (e.g., 22:00 to 06:00)
- **Profile-based**: Schedule is configured at the profile level and applies to all linked sockets
- **User override protection**: Manual socket state changes are preserved between scheduled events
- **Duplicate prevention**: Each schedule event only applies once per day
- **Cron delay tolerance**: Handles delays in cron execution (5-minute tolerance window)
- **Automatic processing**: Schedules are processed every minute via Laravel scheduler

## How it Works

1. **Profile Configuration**: Users enable scheduling in a profile and set:
   - Turn on time
   - Turn off time  
   - Active days of the week

2. **Socket Linking**: Any socket linked to a profile with scheduling enabled will be controlled automatically

3. **Schedule Processing**: Every minute, the system:
   - Checks if any profiles have scheduled events due (within 5-minute tolerance)
   - Applies the schedule only if it hasn't been applied today
   - Updates socket states only if they don't match the scheduled action
   - Records when each schedule was applied to prevent duplicates

4. **User Override**: Between scheduled events, users can manually change socket states without interference

## Database Changes

New columns added to `profiles` table:
- `scheduling_enabled` (boolean): Whether scheduling is active for this profile
- `turn_on_time` (time): Time to turn sockets on
- `turn_off_time` (time): Time to turn sockets off  
- `schedule_days` (json): Array of active days ['monday', 'tuesday', etc.]
- `last_turn_on_applied` (timestamp): When the turn on schedule was last applied
- `last_turn_off_applied` (timestamp): When the turn off schedule was last applied

## Schedule Application Logic

### Timing Rules
- Schedules only apply at the exact scheduled time ± 5 minutes tolerance
- Each schedule event (turn on/off) can only be applied once per day
- Manual user changes between scheduled events are preserved
- The system tracks when each schedule was last applied

### Example Timeline
```
08:00 - Turn ON schedule applies (if socket is OFF)
08:01 - No action (schedule already applied today)
10:00 - User manually turns OFF socket
12:00 - No action (respects user override)
18:00 - Turn OFF schedule applies (if socket is ON)
18:01 - No action (schedule already applied today)
20:00 - User manually turns ON socket  
22:00 - No action (respects user override until next day)
```

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
- `shouldBeOn()`: Check if profile should theoretically be on at current time
- `shouldApplyTurnOn($tolerance)`: Check if turn ON schedule should be applied now
- `shouldApplyTurnOff($tolerance)`: Check if turn OFF schedule should be applied now
- `applySchedule()`: Apply schedule if due (returns detailed result array)
- `getNextScheduleChange()`: Get info about next scheduled state change

### Socket Methods  
- `isScheduled()`: Check if socket has scheduling enabled
- `getScheduledState()`: Get theoretical state according to schedule
- `getPendingScheduleAction()`: Check if a schedule action is pending
- `isInSync()`: Check if current state matches theoretical scheduled state
- `getNextScheduleChange()`: Get next schedule change for this socket

## Commands

### Manual Schedule Processing
```bash
# Process schedules immediately
php artisan schedules:process

# Dry run to see what would change
php artisan schedules:process --dry-run

# Test schedule at specific time
php artisan test:schedule-at-time 08:00
php artisan test:schedule-at-time 18:00 --date=2025-08-15
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

- **Job**: `ProcessSocketSchedulesJob` handles the actual processing with tolerance and duplicate prevention
- **Command**: `ProcessSocketSchedules` provides manual execution and dry-run capability
- **Test Command**: `TestScheduleAtTime` allows testing schedules at specific times
- **Scheduler**: Configured in `routes/console.php` to run every minute
- **Models**: Enhanced `Profile` and `Socket` models with scheduling methods
- **UI**: Extended Filament resource with scheduling form fields

## Testing

Run the test suite to verify scheduling functionality:

```bash
php artisan test --filter=SocketSchedulingTest
```

## Benefits of This Approach

1. **User Control**: Users can override schedules manually without interference
2. **Reliability**: Handles cron delays and prevents duplicate applications
3. **Predictability**: Schedules only apply at designated times
4. **Flexibility**: Users maintain full control between scheduled events
5. **Efficiency**: No unnecessary state changes when sockets are already in the correct state

## Notes

- Schedules are processed every minute but only apply at designated times ± 5 minutes
- Each schedule event (turn on/off) applies only once per day
- Manual user changes between scheduled events are preserved
- If no `schedule_days` are specified, the schedule applies to all days
- Sockets without a linked profile or with scheduling disabled are not affected
- The system logs schedule processing activity for debugging
- Tolerance window accounts for potential cron execution delays
