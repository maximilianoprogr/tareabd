# Proyecto INF-239: Gestión de Artículos

## Integrantes
- Fabian San Martin - 202304650-7
- Maximiliano Yáñez - 202304626-4

Intrucciones:
1. Para correr el proyecto se necesita XAMPP instalado y configurado (con MYsql y Apache en admin).
2. En XAMPP, darle a "start" a los modulos Apache y MySQL.
3. En la direccion donde se instalo XAMPP (generalmente en 'C:\xampp') ir a la carpeta 'htdocs' ('C:\xampp\htdocs'). Si no tienes XAMPP
en el disco 'C:' ahi te las arreglas tu solo.
4. Dentro de la carpeta 'htdocs' extraer el archivo compimido. Deberia ahora haber una carpeta llamada 'tareabd'.
5. Dirigirce a 'http://localhost/phpmyadmin/'. Ahi crearemos y poblaremos la Base de Datos.
6. Dentro de 'C:\xampp\htdocs\tareabd\bd' hay una query llamada 'CREATE_LATERO.sql', copiar y pegar su contenido en la seccion de 'SQL'
de PhpMyAdmin, luego darle a continuar. Esto deberia haber creado todos los elementos de la Base de Datos.
7. Finalmente podemos dirigirnos a la pagina. Ir a la url 'http://localhost/tareabd'. (Si no funciona recuerda tener encendido Apache y
MySQL Server en XAMPP)
8. Enjoy
 
Lenguajes usados

- Bootstrap: 
- CSS y HTML: 
- JavaScript: 
- MySQL y SQL: 
- PHP: 

IMPORTANTE:
Hay un usuario default que es 'Jefe de Comite' donde todas las credenciales son 1 (nombre 1, rut 1, contraseña 1, etc.) para un uso
mas conveniente. Con ese usuario puede ver las funciones bloqueadas por accesos.
Todos los view, funtions, etc. estan dentro de la carpeta 'bd', ya fueron invocados por el query para crear la base de datos.

## Funciones
Se uso un procedimiento almacenado en "gestionar_asignaciones.php", la funcion AsignarArticuloRevisor.sql,
para asignar un artículo a un revisor, validando que el revisor no sea autor del artículo y que los tópicos coincidan.

Se uso un trigger en la tabla "usuarios", llamado after_usuario_tipo_update, 
para eliminar registros relacionados cuando cambia el tipo de usuario.

Se uso un trigger en la tabla "autor_articulo", llamado before_insert_autor_articulo,
 para validar que el autor exista antes de asociarlo a un artículo.

Se uso una View en "login.php", llamada vista_usuarios_login,
 para obtener información de los usuarios y su rol.

Se uso una Function en "contar_articulos_a_revisar.sql", 
llamada contar_articulos_a_revisar, para contar los artículos asignados a un revisor.

