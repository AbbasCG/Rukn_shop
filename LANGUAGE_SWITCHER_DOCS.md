# Language Switcher Implementation

## Summary

A full language switcher has been added to the header navigation with support for English and Arabic. The switcher includes:
- ✅ Desktop dropdown menu with globe icon
- ✅ Mobile menu language selection
- ✅ Automatic locale persistence
- ✅ URL query parameter handling
- ✅ Session-based storage
- ✅ Checkmark indicator for active language

## Files Modified/Created

### Created:
1. **`app/Http/Middleware/SetLocale.php`** - Middleware to handle locale detection and persistence

### Updated:
1. **`resources/views/layouts/navigation.blade.php`** - Added language switcher to desktop and mobile menus
2. **`bootstrap/app.php`** - Registered SetLocale middleware
3. **`config/app.php`** - Added `supported_locales` configuration

## Features

### Desktop Menu
- **Location**: Next to the shopping cart icon in the header
- **Icon**: Globe icon that opens a dropdown menu
- **Options**: 
  - 🇬🇧 English
  - 🇸🇦 العربية (Arabic)
- **Active Indicator**: Checkmark (✓) shows the current language

### Mobile Menu
- **Location**: In the responsive menu below shopping cart
- **Format**: Expandable language section with two clickable options
- **Styling**: Same visual consistency as desktop version

## How It Works

1. **User clicks language option** → URL parameter `?locale=en` or `?locale=ar` is added
2. **SetLocale Middleware** intercepts the request:
   - Reads `locale` query parameter
   - Sets application locale with `app()->setLocale()`
   - Stores preference in session for persistence
3. **Page reloads** with:
   - All text in selected language
   - RTL layout (if Arabic is selected)
   - Language preference remembered for future visits

## Configuration

### Supported Locales
Located in `config/app.php`:
```php
'supported_locales' => ['en', 'ar'],
```

To add more languages:
1. Create translation files in `resources/lang/{locale}/`
2. Add locale code to `supported_locales` array
3. Update language switcher in `navigation.blade.php`

## Usage Examples

### Switching Language via URL
```
/home?locale=en  // Switch to English
/home?locale=ar  // Switch to Arabic
```

### Checking Current Locale in Blade
```php
@if(app()->getLocale() === 'ar')
    <!-- RTL content -->
@else
    <!-- LTR content -->
@endif
```

### Getting Language Preference in PHP
```php
$currentLocale = app()->getLocale();  // Returns: 'en' or 'ar'
$sessionLocale = session('locale');    // Returns stored preference
```

## Locale Persistence

The middleware automatically handles persistence through:

1. **Session Storage** - Preference saved for the user session
2. **Query Parameter** - Temporary override via `?locale=xx`
3. **Config Default** - Falls back to `APP_LOCALE` if no preference

### Priority Order:
1. URL query parameter (`?locale=xx`)
2. Session value (`session('locale')`)
3. Application config default (`APP_LOCALE`)

## Styling Details

### Desktop Dropdown
- **Button**: Circular icon button with hover effects
- **Dropdown**: Right-aligned, 160px width, white background
- **Items**: 
  - Flex layout with flag emoji + text
  - Highlighted background for active language
  - Checkmark icon on the right for active selection
  - Smooth transitions

### Mobile Menu
- **Section**: Labeled "Language" with uppercase styling
- **Items**: Full-width clickable options
- **Active State**: Bold background color
- **Touch-friendly**: Larger padding for mobile

## Browser Behavior

### URL Handling
The switcher uses `request()->fullUrlWithQuery(['locale' => 'xx'])` to:
- Preserve all current query parameters
- Replace or add the `locale` parameter
- Maintain page state while switching language

### Session Handling
The locale preference persists across page navigation until:
- Browser session ends (for session-based storage)
- User explicitly changes language
- User clears browser cache/cookies

## RTL Integration

When Arabic is selected:
- Layout automatically switches to RTL via `dir="rtl"` attribute
- Text aligns to right
- Flex directions reverse
- Components maintain proper spacing
- Mobile menu adapts for RTL reading

## Testing Checklist

- [ ] Click language button in desktop menu
- [ ] Verify dropdown appears with both language options
- [ ] Select English → Page switches to English
- [ ] Select Arabic → Page switches to Arabic with RTL
- [ ] Refresh page → Language preference is maintained
- [ ] Navigate to different page → Language persists
- [ ] Test mobile menu language selection
- [ ] Verify checkmark shows on active language
- [ ] Verify no console errors

## Troubleshooting

### Language not switching?
- Check middleware is registered in `bootstrap/app.php`
- Verify `SetLocale` middleware file exists
- Check browser console for JavaScript errors
- Clear browser cache and session

### Language not persisting?
- Verify sessions are properly configured
- Check `APP_FALLBACK_LOCALE` in `.env`
- Ensure cookies are enabled in browser

### RTL not working?
- Verify home.blade.php has `dir` attributes on sections
- Check Tailwind CSS is compiled with RTL support
- Verify `app()->getLocale() === 'ar'` condition works

## Code Examples

### Accessing Current Language
```php
<!-- In Blade templates -->
{{ app()->getLocale() }}  // Output: 'en' or 'ar'
```

### Language-specific Links
```php
<a href="{{ request()->fullUrlWithQuery(['locale' => 'ar']) }}">
    عربي
</a>
```

### Conditional Display
```php
@if(app()->getLocale() === 'ar')
    <p>مرحبا بك</p>
@else
    <p>Welcome</p>
@endif
```

## Performance Impact

- **Minimal**: Middleware runs on every request but uses simple string comparison
- **Session storage**: No database queries, uses Laravel sessions
- **No API calls**: Everything is server-side
- **Page reload**: Required for layout changes (necessary for RTL switch)

## Future Enhancements

1. **AJAX Language Switch** - Switch without full page reload
2. **User Preference Storage** - Save in user profile for logged-in users
3. **Browser Language Detection** - Auto-detect from browser settings
4. **More Languages** - Add support for French, German, Spanish, etc.
5. **Language Flags** - Replace emoji with proper flag images
6. **Keyboard Navigation** - Add arrow key navigation to dropdown

## Notes

- The language switcher respects the existing navigation structure
- All existing functionality remains unchanged
- Language preference is user-specific (session-based)
- Compatible with both authenticated and guest users
- Mobile-first responsive design maintained
- No external dependencies added
