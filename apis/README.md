# Panel de Administración - Gestión de Usuarios

## 📋 Descripción

Panel de administración moderno y seguro para gestionar usuarios del sistema. Solo usuarios con rol `admin` pueden acceder.

## 🚀 Características

✅ **Autenticación segura** - Login con sesiones PHP  
✅ **CRUD completo** - Crear, leer, actualizar y eliminar usuarios  
✅ **Gestión de roles** - Asignar roles (admin/user)  
✅ **Control de estado** - Activar/desactivar usuarios  
✅ **Contraseñas hasheadas** - Seguridad con bcrypt  
✅ **Interfaz intuitiva** - Diseño responsivo y moderno  
✅ **Validaciones** - Prevención de duplicados y campos requeridos  

## 📁 Archivos

```
apis/
├── admin-panel.html      # Interfaz del panel (abrir en navegador)
├── auth.php              # API con autenticación (sesiones)
├── usuarios.php          # API CRUD sin autenticación
├── config.php            # Configuración de BD
├── cors.php              # Headers CORS
└── README.md             # Este archivo
```

## 🔧 Instalación

### 1. Crear primer usuario admin

Ejecuta este SQL en tu base de datos `restaurante`:

```sql
-- Crear tabla si no existe (se crea automáticamente)
CREATE TABLE IF NOT EXISTS usuarios (
  id INT AUTO_INCREMENT PRIMARY KEY,
  nombre VARCHAR(255) NOT NULL,
  usuario VARCHAR(255) UNIQUE NOT NULL,
  contrasena VARCHAR(255) NOT NULL,
  rol VARCHAR(50) DEFAULT 'user',
  activo TINYINT(1) DEFAULT 1
) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;

-- Insertar primer admin (contraseña: admin123)
INSERT INTO usuarios (nombre, usuario, contrasena, rol, activo) 
VALUES ('Administrador', 'admin', '$2y$10$YOUR_HASHED_PASSWORD', 'admin', 1);
```

**Para generar el hash de contraseña, usa PHP:**

```php
echo password_hash('admin123', PASSWORD_BCRYPT);
```

O simplemente accede al panel y crea el usuario desde la interfaz (después de editar auth.php para permitir el primer acceso).

### 2. Acceder al panel

```
http://localhost/9amm/IA/apis/admin-panel.html
```

**Credenciales iniciales:**
- Usuario: `admin`
- Contraseña: `admin123`

## 🛡️ Seguridad

### Características de seguridad implementadas:

1. **Sesiones PHP** - Autenticación basada en sesiones del servidor
2. **Password Hashing** - Contraseñas con bcrypt (PASSWORD_BCRYPT)
3. **Validación de rol** - Solo admins pueden acceder
4. **Prepared Statements** - Protección contra SQL Injection
5. **Charset UTF-8MB4** - Prevención de ataques de encoding
6. **Usuarios únicos** - No se pueden crear duplicados
7. **Control de estado** - Deshabilitar usuarios sin eliminarlos

## 📡 API Endpoints

### `auth.php` (Con autenticación de sesión)

**POST /apis/auth.php**
```json
// Login
{
  "login": true,
  "usuario": "admin",
  "contrasena": "admin123"
}

// Logout
{
  "logout": true
}
```

**GET /apis/auth.php** - Listar usuarios (requiere sesión)
**POST /apis/auth.php** - Crear usuario (requiere sesión)
**PUT /apis/auth.php** - Actualizar usuario (requiere sesión)
**DELETE /apis/auth.php?id=1** - Eliminar usuario (requiere sesión)

### `usuarios.php` (Sin autenticación)

**GET /apis/usuarios.php?rol=admin** - Lista usuarios (solo header o query param)
**POST /apis/usuarios.php** - Crear usuario
**PUT /apis/usuarios.php** - Actualizar usuario
**DELETE /apis/usuarios.php?id=1** - Eliminar usuario

*Nota: `usuarios.php` valida `rol=admin` por headers o body (menos seguro)*

## 🎯 Casos de uso

### Crear usuario
```javascript
fetch('http://localhost/9amm/IA/apis/auth.php', {
  method: 'POST',
  credentials: 'include',
  headers: { 'Content-Type': 'application/json' },
  body: JSON.stringify({
    nombre: 'Juan Pérez',
    usuario: 'juan.perez',
    contrasena: 'micontraseña123',
    rol: 'user',
    activo: 1
  })
})
```

### Listar usuarios
```javascript
fetch('http://localhost/9amm/IA/apis/auth.php', {
  credentials: 'include'
})
.then(r => r.json())
```

### Actualizar usuario
```javascript
fetch('http://localhost/9amm/IA/apis/auth.php', {
  method: 'PUT',
  credentials: 'include',
  headers: { 'Content-Type': 'application/json' },
  body: JSON.stringify({
    id: 1,
    nombre: 'Juan Pérez Actualizado',
    usuario: 'juan.perez',
    rol: 'admin',
    activo: 1
  })
})
```

## 🐛 Troubleshooting

### "Acceso denegado"
- Verifica que el usuario sea admin
- Comprueba que esté activo (activo = 1)
- Verifica la contraseña (usa bcrypt)

### "El usuario ya existe"
- El nombre de usuario debe ser único
- Cambia el nombre de usuario a uno que no exista

### "Error de conexión"
- Verifica que MySQL esté corriendo
- Comprueba credenciales en `config.php`
- Asegúrate de que la base de datos `restaurante` existe

### Sesión expira
- Las sesiones tienen timeout por defecto
- Realiza login nuevamente
- En producción, configura `session.gc_maxlifetime` en php.ini

## 📚 Integración con Angular/Ionic

Si necesitas integrar con Angular:

```typescript
// auth.service.ts
export class AuthService {
  login(usuario: string, contrasena: string) {
    return this.http.post('/apis/auth.php', {
      login: true,
      usuario,
      contrasena
    }, { withCredentials: true });
  }

  getUsuarios() {
    return this.http.get('/apis/auth.php', { withCredentials: true });
  }

  logout() {
    return this.http.post('/apis/auth.php', 
      { logout: true }, 
      { withCredentials: true }
    );
  }
}
```

## 📝 Notas

- El panel es **responsivo** y funciona en móvil
- Todos los mensajes están en **español**
- Las contraseñas nuevas no se envían en respuestas
- Al editar, dejar contraseña en blanco mantiene la actual
- Los usuarios inactivos (activo=0) no pueden hacer login

## 🔐 Recomendaciones de producción

1. **HTTPS** - Usar siempre HTTPS en producción
2. **CORS** - Configurar CORS adecuadamente (no usar `*`)
3. **Rate limiting** - Implementar límite de intentos de login
4. **Logs** - Registrar accesos y cambios de datos
5. **2FA** - Considerar autenticación de dos factores
6. **Backups** - Hacer backups regulares de la BD
7. **WAF** - Usar Web Application Firewall

## 📞 Soporte

Para preguntas o problemas, contacta al administrador del sistema.

---

**Última actualización:** 2026-06-01  
**Versión:** 1.0
