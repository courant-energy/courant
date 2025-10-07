# Seed User Devices Command

This document explains how to use the `seed:user-devices` artisan command to generate IoT devices with profiles and readings for existing users.

## Overview

The `seed:user-devices` command creates:
- 2-3 IoT devices per user (configurable)
- 2 sockets per device (each with unique profiles)
- 2 months of realistic sensor readings (every 30 minutes)
- Various appliance types with realistic usage patterns

## Usage

### List Available Users
```bash
php artisan seed:user-devices --list-users
```

### Seed Devices by User ID
```bash
php artisan seed:user-devices --user-id=1
```

### Seed Devices by Email
```bash
php artisan seed:user-devices --email=user@example.com
```

### Specify Number of Devices
```bash
php artisan seed:user-devices --user-id=1 --devices=3
```

## Command Options

| Option | Description | Default |
|--------|-------------|---------|
| `--user-id` | ID of the existing user | Required (or --email) |
| `--email` | Email of the existing user | Required (or --user-id) |
| `--devices` | Number of devices to create (2-3) | Random (2-3) |
| `--list-users` | List available users | - |

## Generated Data Structure

### IoT Devices
- Realistic device names and types
- Unique device UUIDs with SEED prefix
- Random status (online/offline)
- Last seen timestamps

### Profiles
The command includes 8 different appliance types:
- **Coffee Machine**: Morning/evening usage pattern
- **Refrigerator**: Constant cycling pattern
- **Computer Workstation**: Business hours usage
- **Air Conditioner**: Temperature-dependent usage
- **Microwave Oven**: Meal times usage pattern
- **Washing Machine**: Weekly cycles pattern
- **Electric Heater**: Temperature-dependent usage
- **LED Television**: Evening entertainment pattern

### Readings
- **Duration**: 60 days (2 months) of historical data
- **Interval**: Every 30 minutes (2,880 readings per profile)
- **Parameters**: Voltage, current, power, energy, frequency, power factor
- **Patterns**: Realistic usage based on appliance type and time

### Features
- **Scheduling**: 35% of profiles have automatic scheduling configured
- **Notifications**: Configurable alert preferences and sensitivity
- **Locations**: Varied realistic locations per appliance type
- **Usage Patterns**: Time-based realistic power consumption

## Examples

### Example 1: Quick Setup for Testing
```bash
# List users to find a suitable one
php artisan seed:user-devices --list-users

# Seed 2 devices for user ID 5
php artisan seed:user-devices --user-id=5
```

### Example 2: Full Setup for Demo
```bash
# Create 3 devices with full data for demo purposes
php artisan seed:user-devices --email=demo@example.com --devices=3
```

### Example 3: Development Environment
```bash
# Seed multiple users for comprehensive testing
php artisan seed:user-devices --user-id=1 --devices=2
php artisan seed:user-devices --user-id=2 --devices=3
php artisan seed:user-devices --user-id=3 --devices=2
```

## Performance Notes

- Generating 2 months of readings takes approximately 30-60 seconds per device
- Uses batch inserts for optimal database performance
- Database transactions ensure data consistency
- Memory management prevents issues with large datasets

## Sample Output

```
✓ Found user: John Doe (john@example.com)
🚀 Starting device seeding for user: john@example.com
📋 Creating 3 devices with 2 sockets each and 2 months of readings
⏳ This may take a few minutes...

Creating device 1/3...
  ✓ Socket 1: Coffee Machine - Kitchen (D1S1) (2880 readings)
  ✓ Socket 2: Refrigerator - Kitchen (D1S2) (2880 readings)

Creating device 2/3...
  ✓ Socket 1: Computer Workstation - Office (D2S1) (2880 readings)
  ✓ Socket 2: Air Conditioner - Living Room (D2S2) (2880 readings)

Creating device 3/3...
  ✓ Socket 1: Microwave Oven - Kitchen (D3S1) (2880 readings)
  ✓ Socket 2: LED Television - Living Room (D3S2) (2880 readings)

🎉 Device seeding completed successfully!

🎯 === DEVICE SEEDING SUMMARY ===
👤 User: John Doe (john@example.com)
📱 IoT Devices Created: 3
🔌 Sockets Created: 6
📋 Profiles Created: 6
📊 Readings Generated: 17,280
⏱️  Processing Time: 45 seconds
```

## Error Handling

The command includes comprehensive error handling:
- Validates user existence before processing
- Uses database transactions for rollback on failure
- Provides detailed error messages
- Logs errors for debugging

## Integration

This command works seamlessly with:
- Existing database structure
- Current model relationships
- Profile scheduling system
- Anomaly detection features
- Energy consumption calculations