# Proyecto INF-239: Gestión de Artículos

## Integrantes
- Fabian San Martin - 202304650-7
- Nombre 2 - ROL2

## Instrucciones
1. Importar la base de datos desde la carpeta `BD` usando el archivo `database.sql`.
2. Configurar la conexión a la base de datos en `conexion.php`.
3. Abrir el proyecto en un servidor local (por ejemplo, XAMPP).
4. Acceder a `index.php` para iniciar sesión o registrarse.
5. Navegar por las secciones para gestionar artículos, revisores y realizar búsquedas avanzadas.

## Supuestos
- Los correos electrónicos de notificación se simulan con mensajes en pantalla.
- Los datos de prueba están incluidos en `generated_data.sql` y deben ser importados para pruebas completas.
- Los usuarios deben registrarse con roles específicos (autor o revisor) para acceder a las funcionalidades correspondientes.

## Funcionalidades
- **Login y Registro**: Sistema de autenticación con contraseñas cifradas.
- **Gestión de Artículos**: Crear, leer, actualizar y eliminar artículos.
- **Gestión de Revisores**: Agregar, editar y eliminar revisores.
- **Asignación Automática**: Asignación de artículos a revisores basada en tópicos.
- **Búsqueda Avanzada**: Filtrar artículos por autor, fecha, tópicos y revisores.
- **Protección contra Inyecciones SQL**: Uso de sentencias preparadas en todas las consultas.

## Pasos Adicionales
- Para probar la asignación automática, ejecutar `asignar_automatico.php`.
- Verificar artículos con menos de dos revisores en la sección correspondiente.
- Utilizar Bootstrap para mejorar la presentación visual del proyecto.

## Ejemplos de Uso

### 1. Login y Registro
- **Registro**: Acceder a `register.html`, completar el formulario con un ID de usuario, contraseña y rol (autor o revisor), y presionar "Registrar".
- **Login**: Acceder a `login.html`, ingresar el ID de usuario y contraseña, y presionar "Iniciar sesión".

### 2. Gestión de Artículos
- **Crear Artículo**: Navegar a la sección de creación de artículos, completar el formulario con título, resumen, fecha de envío y tópicos, y presionar "Crear".
- **Editar Artículo**: Seleccionar un artículo existente, modificar los campos necesarios y guardar los cambios.
- **Eliminar Artículo**: Seleccionar un artículo y presionar "Eliminar".

### 3. Gestión de Revisores
- **Agregar Revisor**: Completar el formulario con RUT, nombre, email y tópicos asignados, y presionar "Agregar".
- **Editar Revisor**: Modificar los datos de un revisor existente y guardar los cambios.
- **Eliminar Revisor**: Seleccionar un revisor y presionar "Eliminar" (solo si no tiene artículos asignados).

### 4. Asignación Automática
- **Ejecutar Asignación**: Acceder a `asignar_automatico.php` para asignar automáticamente artículos a revisores según los tópicos.
- **Verificar Resultados**: Revisar los mensajes en pantalla para identificar artículos con menos de dos revisores.

### 5. Búsqueda Avanzada
- **Filtrar Artículos**: Utilizar los filtros disponibles (autor, fecha, tópicos, revisores) en la sección de búsqueda avanzada y presionar "Buscar".

## Pasos para Probar el Sistema
1. **Configurar el Entorno**:
   - Importar la base de datos desde `BD/database.sql`.
   - Configurar la conexión en `php/conexion.php`.
   - Iniciar el servidor local (por ejemplo, XAMPP).

2. **Registrar Usuarios**:
   - Crear al menos un usuario con rol "autor" y otro con rol "revisor".

3. **Crear Artículos**:
   - Iniciar sesión como autor y crear varios artículos con diferentes tópicos.

4. **Asignar Revisores**:
   - Iniciar sesión como administrador y asignar revisores a los artículos manualmente o utilizando la asignación automática.

5. **Realizar Búsquedas**:
   - Probar la funcionalidad de búsqueda avanzada utilizando diferentes filtros.

6. **Validar Seguridad**:
   - Verificar que las entradas de usuario estén protegidas contra inyecciones SQL.

7. **Probar Diseño**:
   - Navegar por las páginas para confirmar que el diseño es consistente y responsivo gracias a Bootstrap.

Se uso un prodecedimiento almacenado en "gestionar_asignaciones.php", la funcion AsignarArticuloRevisor.sql
Se uso una View en "login.php".
Hay un tigger en la tabla "autor_articulo" que chuequea su uso correcto.
