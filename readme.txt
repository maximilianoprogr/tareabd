# Proyecto INF-239: Gestión de Artículos

## Integrantes
- Fabian San Martin - 202304650-7
- Maximiliano Yáñez - 202304626-4

Intrucciones:
1. Para correr el proyecto se necesita XAMPP instalado y configurado (con MYsql y Apache en admin).
2. En XAMPP, darle a "start" a los modulos Apache y MySQL.
3. En la direccion donde se instalo XAMPP (generalmente en 'C:\xampp') ir a la carpeta 'htdocs' ('C:\xampp\htdocs').
4. Dentro de la carpeta 'htdocs' extraer el archivo compimido. Deberia ahora haber una carpeta llamada 'tareabd'.
5. Dirigirce a 'http://localhost/phpmyadmin/'. Ahi crearemos y poblaremos la Base de Datos.
6. 
y dentro de el buscar htdocs y guardar la carpeta `proyecto_php` dentro de el. Una vez terminado esto ir a xamps y darle a admin.
Se abrira una pagina web, tendras que buscar donde dice phpmyadmin y darle a mysql. Luego tendras que poner los
create table e insert y darle a ok. Con esto ya tienes la mitad. Para probar el proyecto luego tendras ir a google y 
buscar http://localhost/proyecto_php/php/login.php, de ahi estás listo con el proyecto y solo hay que usarlo.
 
 Lenguajes usados

- Bootstrap: 
- CSS y HTML: 
- JavaScript: 
- MySQL y SQL: 
- PHP: 

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

