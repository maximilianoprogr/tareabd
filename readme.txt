# Proyecto INF-239: Gestión de Artículos

## Integrantes
- Nombre 1 - ROL1
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