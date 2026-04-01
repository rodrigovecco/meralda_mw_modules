# Users2 - Modernized Users Module

> **Version**: 2026-03  
> **Requires**: PHP 7.4+  
> **Extends**: `mwmod_mw_users_*`

## Description

`users2` is a modern layer on top of the existing `users` module that adds:

- ✅ PHP 7.4+ type hints
- ✅ Helper methods with clear names (camelCase)
- ✅ Extensible hooks for customization
- ✅ Stricter password policy by default
- ✅ Better PHPDoc documentation
- ✅ Backward compatibility with `users`

## Structure

```
users2/
├── usersmanabs.php       # Abstract manager (extends def_usersman)
├── usersman.php          # Concrete manager
├── userabs.php           # Abstract user (extends user)
├── user.php              # Concrete user
├── userdata.php          # Form validation
├── passpolicy.php        # Password policy
├── def/                  # Default implementations
│   ├── usersman.php      # Default manager with common config
│   └── userdata.php      # Default userdata with avatar config
└── README.md             # This documentation
```

## Basic Usage

### Configure in your project

```php
<?php
// src/app/managers/user.php

// Use users2 def (includes common defaults)
$subman = new mwmod_mw_users2_def_usersman($this, "users");

// Or use base users2 for more control
// $subman = new mwmod_mw_users2_usersman($this, "users");

// Configure roles
$rolsman = new mwmod_mw_users_rols_rolsman($subman);
$subman->set_rols_man($rolsman);
$rolsman->add_item(new mwmod_mw_users_rols_rol("admin", "Administrator", $rolsman));
$rolsman->add_item(new mwmod_mw_users_rols_rol("user", "User", $rolsman));

// Configure permissions
$permissionsman = new mwmod_mw_users_permissions_permissionsman($subman, $rolsman);
$subman->set_permissions_man($permissionsman);
$permissionsman->add_item(new mwmod_mw_users_permissions_permission(
    "admin", "Administrate", "admin", $permissionsman
));
$permissionsman->init_rols();
```

### Authentication

```php
// New method with hooks
$user = $man->authenticate('user@email.com', 'password123');

// Or backward compatible method
$user = $man->login('user@email.com', 'password123');

// Check session
if ($man->isUserLogged()) {
    $currentUser = $man->getCurrentUser();
}

// Logout
$man->logoutCurrentUser();
```

### Get users

```php
// With type hints
$user = $man->getUserById(123);          // ?mwmod_mw_users2_user
$user = $man->getUserByIdName('admin');  // ?mwmod_mw_users2_user
$users = $man->getActiveUsers();         // array<int, mwmod_mw_users2_user>
$admins = $man->getUsersByRole('admin'); // array<int, mwmod_mw_users2_user>
```

### User Object

```php
// Identity
$user->getId();           // int
$user->getUsername();     // string
$user->getFullName();     // string
$user->getEmail();        // ?string

// Status
$user->isActive();        // bool
$user->isMainAdmin();     // bool
$user->canLogin();        // bool

// Roles
$user->hasRole('admin');                  // bool
$user->hasAnyRole(['admin', 'manager']);  // bool
$user->hasAllRoles(['user', 'editor']);   // bool
$user->getRoleCodes();                    // string[]

// Permisos
$user->hasPermission('edit_users');                          // bool
$user->hasAnyPermission(['edit', 'view']);                   // bool
$user->hasAllPermissions(['create_user', 'delete_user']);    // bool

// Serialización
$user->toArray();    // array para APIs
$user->toJson();     // string JSON
```

### Permisos a nivel Manager

```php
// Verificar OR (cualquier permiso)
$man->hasPermission(['edit', 'view']);

// Verificar AND (todos los permisos)
$man->hasAllPermissions(['create', 'delete']);
```

### Política de Contraseñas

```php
$policy = $man->get_pass_policy();

// Validar contraseña
$result = $policy->validatePassword('miPassword123!');
// ['valid' => true, 'errors' => []]

// Generar contraseña segura
$newPass = $policy->generateSecurePassword(16);

// Calcular fortaleza
$score = $policy->calculateStrength('Test123!@#');  // 0-100
$label = $policy->getStrengthLabel($score);         // "Fuerte"
```

## Hooks de Extensión

### En el Manager

```php
class MyUserManager extends mwmod_mw_users2_usersman {

    // Antes de login (captcha, rate limit, etc.)
    protected function onBeforeLogin(string $username, array $options): bool {
        // Verificar captcha
        if (!$this->verifyCaptcha()) {
            return false;
        }
        return true;
    }

    // Después de login exitoso (logging, notificaciones)
    protected function onAfterLoginSuccess(object $user, array $options): void {
        $this->logLoginEvent($user);
        $this->sendLoginNotification($user);
    }

    // Después de login fallido (alertas de seguridad)
    protected function onLoginFailed(string $username, array $options): void {
        $this->logFailedAttempt($username);
    }
}
```

### En el Usuario

```php
class MyUser extends mwmod_mw_users2_user {

    // Después de login
    protected function onAfterLogin(): void {
        // Actualizar contador de logins
        // Cargar preferencias
    }
}
```

## Diferencias con users

| Aspecto | users | users2 |
|---------|-------|--------|
| Type hints | ❌ No | ✅ PHP 7.4+ |
| Nombres métodos | snake_case | camelCase (+ compat) |
| `get_user()` retorna | `false` si no existe | `null` |
| Contraseña mínima | 8 chars | 10 chars |
| Hooks extensión | Limitados | Completos |
| Validación password | Básica | Con fortaleza |
| Serialización | Manual | `toArray()`, `toJson()` |

## Compatibilidad

- **Tabla BD**: Usa la misma tabla `users` — sin migraciones
- **Sesiones**: Compatible con estructura de sesión actual
- **Roles/Permisos**: Usa las mismas clases existentes
- **JWT**: Compatible con `mwmod_mw_users_jwt_man`

## Migración desde users

1. Cambiar instanciación del manager:
   ```php
   // Antes
   $subman = new mwmod_mw_users_def_usersman($this, "users");
   // Después
   $subman = new mwmod_mw_users2_usersman($this, "users");
   ```

2. Los métodos antiguos siguen funcionando (compatibilidad)

3. Actualizar código gradualmente para usar nuevos métodos

## Extender users2

```php
<?php
// Tu proyecto: src/app/managers/users/myusersman.php

class myapp_users_usersman extends mwmod_mw_users2_usersman {
    
    // Personalizar seguridad
    protected function applyDefaultSecuritySettings(): void {
        parent::applyDefaultSecuritySettings();
        // Bloqueo más agresivo
        $this->set_disable_login_after_fail(true, 30, 3);
    }
    
    // Agregar funcionalidad
    public function getUsersByDepartment(int $deptId): array {
        $q = $this->new_users_query();
        $q->where->add_where_crit("department_id", $deptId);
        $q->where->add_where_crit("active", 1);
        return $this->get_users_by_query($q) ?: [];
    }
}
```
