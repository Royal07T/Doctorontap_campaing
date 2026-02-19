# Medical Icons System

This directory contains open-source medical SVG icons organized by category. All icons use a consistent outline style and are free for commercial use.

## Directory Structure

```
resources/icons/
├── specializations/    # Doctor specialization icons
├── symptoms/          # Symptom/condition icons
└── README.md         # This file
```

## Available Icons

### Specializations (`resources/icons/specializations/`)

- `stethoscope.svg` - General Practice
- `heart.svg` - Cardiology
- `baby.svg` - Pediatrics
- `skin.svg` - Dermatology
- `brain.svg` - Neurology
- `pregnancy.svg` - OB/GYN
- `bone.svg` - Orthopedics
- `eye.svg` - Ophthalmology
- `ear-nose-throat.svg` - ENT
- `stomach.svg` - Gastroenterology
- `kidney.svg` - Urology

### Symptoms (`resources/icons/symptoms/`)

- `cough.svg` - Cough
- `fever.svg` - Fever
- `headache.svg` - Headache
- `stomach-pain.svg` - Stomach Pain
- `back-pain.svg` - Back Pain
- `chest-pain.svg` - Chest Pain
- `joint-pain.svg` - Joint Pain
- `eye-problems.svg` - Eye Problems
- `ear-pain.svg` - Ear Pain
- `period-doubts-or-pregnancy.svg` - Menstrual Pain / Pregnancy Concerns

## Usage

### Laravel Helper Function

Use the `medical_icon()` helper function to load icons dynamically:

```php
// Basic usage
{!! medical_icon('specializations', 'heart') !!}

// With custom attributes
{!! medical_icon('symptoms', 'cough', ['class' => 'w-6 h-6 text-purple-600']) !!}
```

### Function Signature

```php
medical_icon(string $category, string $key, array $attributes = []): string
```

**Parameters:**
- `$category` - Icon category: `'specializations'` or `'symptoms'`
- `$key` - Icon key/name (filename without .svg extension)
- `$attributes` - Optional array of SVG attributes (e.g., `['class' => 'w-6 h-6', 'fill' => 'currentColor']`)

**Returns:** SVG content as a string (safe for Blade `{!! !!}`)

### Examples

```blade
<!-- Specialization icon -->
<div class="icon-container">
    {!! medical_icon('specializations', 'stethoscope', ['class' => 'w-6 h-6 text-purple-600']) !!}
</div>

<!-- Symptom icon -->
<div class="icon-container">
    {!! medical_icon('symptoms', 'fever', ['class' => 'w-4 h-4 text-red-600']) !!}
</div>
```

## Icon Style

All icons follow these design principles:

- **Outline style** - Consistent stroke-based design
- **24x24 viewBox** - Standardized dimensions
- **Current color** - Icons use `currentColor` for easy theming
- **2px stroke width** - Consistent line weight
- **Rounded line caps** - Professional appearance

## Adding New Icons

1. Create SVG file in appropriate directory (`specializations/` or `symptoms/`)
2. Use consistent naming (lowercase, hyphens for spaces)
3. Follow the outline style guidelines
4. Test with the `medical_icon()` helper function

## License

All icons are open-source and free for commercial use. Icons are created with consistent styling for the DoctorOnTap platform.

