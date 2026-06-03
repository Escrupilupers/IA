# 🚀 GUÍA RÁPIDA - Panel de Administración

## ⚡ Inicio rápido (5 minutos)

### Paso 1: Crear tabla y usuario admin

1. Abre tu cliente MySQL (phpMyAdmin, MySQL Workbench, etc.)
2. Selecciona la base de datos `restaurante`
3. Importa o ejecuta el archivo `init-usuarios.sql`

**O ejecuta directamente:**
```bash
mysql -u root -p restaurante < init-usuarios.sql
```

### Paso 2: Acceder al panel

Abre en tu navegador:
```
http://localhost/9amm/IA/apis/admin-panel.html
```

### Paso 3: Login

- **Usuario:** admin
- **Contraseña:** admin123

¡Listo! Ya puedes gestionar usuarios.

---

## 📖 Lo que puedes hacer

### ✅ Crear usuarios
1. Click en "➕ Nuevo Usuario"
2. Completa los campos
3. Click en "Guardar"

### ✏️ Editar usuarios
1. Click en "Editar" en la fila del usuario
2. Modifica los datos
3. Para cambiar contraseña, llenala; dejarla en blanco mantiene la actual
4. Click en "Guardar"

### 🗑️ Eliminar usuarios
1. Click en "Eliminar" en la fila del usuario
2. Confirma en el diálogo
3. Click en "Eliminar"

### 🔄 Refrescar lista
Click en "🔄 Actualizar" para obtener la lista más reciente

### 🚪 Cerrar sesión
Click en "Cerrar Sesión" en la esquina superior derecha

---

## 🔐 Información de seguridad

- ✅ Solo admins pueden acceder al panel
- ✅ Las contraseñas se guardan con hash bcrypt (seguro)
- ✅ La sesión se mantiene mientras el navegador esté abierto
- ✅ Los usuarios inactivos no pueden hacer login

---

## 📋 Campos de usuario

| Campo | Tipo | Requerido | Notas |
|-------|------|-----------|-------|
| Nombre | Texto | Sí | Nombre completo del usuario |
| Usuario | Texto | Sí | Username único (no puede repetirse) |
| Contraseña | Texto | Sí (crear), No (editar) | Se guarda hasheada |
| Rol | Select | Sí | admin o user |
| Estado | Select | Sí | Activo (1) o Inactivo (0) |

---

## 🐛 Problemas comunes

**"Credenciales inválidas"**
- Verifica usuario y contraseña
- El usuario debe estar activo (Estado = Activo)

**"El usuario ya existe"**
- El nombre de usuario debe ser único
- Intenta con otro nombre de usuario

**"Error de conexión"**
- Verifica que MySQL esté corriendo
- Verifica que exista la base de datos `restaurante`
- Verifica credenciales en `apis/config.php`

---

## 📁 Archivos creados

```
apis/
├── admin-panel.html        ← Panel (ABRE ESTO EN NAVEGADOR)
├── auth.php                ← API con autenticación
├── usuarios.php            ← API CRUD
├── config.php              ← Configuración (ya existía)
├── cors.php                ← CORS (ya existía)
├── init-usuarios.sql       ← Script SQL para inicializar
└── README.md               ← Documentación completa
```

---

## 🎯 Próximos pasos

1. Crea más usuarios con rol "user"
2. Cambia la contraseña del admin inicial
3. Desactiva usuarios que no uses más (mejor que eliminar)
4. En producción: usa HTTPS y configura CORS adecuadamente

---

## 💡 Tips

- Puedes editar usuarios sin cambiar su contraseña (déjala en blanco)
- Los usuarios inactivos aparecen en la lista pero no pueden hacer login
- Las contraseñas no se muestran nunca por seguridad
- El panel se adapta a móvil automáticamente

---

¡Disfruta tu panel de administración! 🎉
