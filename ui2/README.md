# UI2 Module

UI definitions that use `users2` module for My Account (modern JS inputs) and provide modern login/password reset interfaces with floating labels.

## Usage

```php
// Extend from ui2 instead of ui:
class mwap_demo_uiadmin_main extends mwmod_mw_ui2_def_main_admin {
    // Your custom subinterfaces...
}
```

## Features

- **Modern Login UI**: Uses `mw_ui_login2.js` with floating labels and modern validation
- **Password Reset**: Modern UI for password reset request and change
- **Security Enforcement**: Forces security actions (password change, 2FA) when required
- **users2 Integration**: Uses `mwmod_mw_users2_ui_myaccount` for My Account

## Hierarchy

```
mwmod_mw_uitemplates_sbadmin_main
└── mwmod_mw_ui2_main (base with security enforcement layer)
    └── mwmod_mw_ui2_def_main_def (default subinterfaces)
        └── mwmod_mw_ui2_def_main_admin (admin interface)
```

## Security Enforcement

The `mwmod_mw_ui2_main` class includes a security enforcement layer that:

1. Detects if user has pending mandatory actions (forced password change, etc.)
2. Only allows subinterfaces that declare themselves compatible via `isAllowedDuringForcedSecurityAction()`
3. Forces the appropriate security subinterface otherwise

## Subinterfaces

| Code | Class | Description |
|------|-------|-------------|
| `login` | `mwmod_mw_ui2_sub_uilogin` | Modern login with floating labels |
| `rememberlogindata` | `mwmod_mw_ui2_sub_rememberlogindata` | Password reset/change |
| `myaccount` | `mwmod_mw_users2_ui_myaccount_myaccount` | Modern My Account |
| `forcechangepass` | `mwmod_mw_users2_ui_forcechangepass` | Forced password change |

## JavaScript

The login UI uses `mw_ui_login2` which extends `mw_ui_login` to work with the modern JS inputs system (`mw_datainput_item_frmonpanel`).
