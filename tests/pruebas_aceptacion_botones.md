# Pruebas de Aceptación para los Botones

## 1. Enviar Artículo
**Criterios de Aceptación:**
- El botón debe estar visible en la interfaz principal después de iniciar sesión.
- Al hacer clic, debe redirigir a la página `enviar_articulo.php`.
- La página debe permitir al usuario cargar un archivo y enviar un formulario.

---

## 2. Acceso al Artículo
**Criterios de Aceptación:**
- El botón debe estar visible en la interfaz principal después de iniciar sesión.
- Al hacer clic, debe redirigir a la página `acceso_articulo.php`.
- La página debe mostrar una lista de artículos enviados por el usuario.

---

## 3. Gestión de Revisores
**Criterios de Aceptación:**
- El botón debe estar visible en la interfaz principal después de iniciar sesión.
- Al hacer clic, debe redirigir a la página `gestionar_revisores.php`.
- La página debe permitir:
  - **Agregar Revisores:**
    - Validar que no existan duplicados por nombre o email.
    - Asegurar que cada revisor tenga al menos un tópico asignado.
    - Enviar un correo al revisor con su `userid` y `password`.
  - **Editar Revisores:**
    - Validar que no existan duplicados por nombre o email al actualizar.
    - Permitir modificar los datos del revisor y sus tópicos asignados.
  - **Eliminar Revisores:**
    - No permitir eliminar revisores con artículos asignados.
    - Mostrar un mensaje claro si no se puede eliminar un revisor.

---

## 4. Asignación de Artículos
**Criterios de Aceptación:**
- El botón debe estar visible en la interfaz principal después de iniciar sesión.
- Al hacer clic, debe redirigir a la página `asignar_articulos.php`.
- La página debe permitir asignar artículos a revisores disponibles.

---

**Nota:** Cada prueba debe ser validada manualmente y documentada con capturas de pantalla del resultado esperado.
