document.addEventListener('DOMContentLoaded', function () {
    const links = document.querySelectorAll('[data-page]');
    const content = document.getElementById('content');

    links.forEach(link => {
        link.addEventListener('click', function (e) {
            e.preventDefault(); // Evitar la recarga de la página
            const page = this.getAttribute('data-page');

            // Cargar la página seleccionada dinámicamente
            fetch(page)
                .then(response => {
                    if (!response.ok) {
                        throw new Error('Error al cargar la página');
                    }
                    return response.text();
                })
                .then(html => {
                    content.innerHTML = html; // Insertar el contenido en el contenedor
                })
                .catch(error => {
                    console.error('Error:', error);
                    content.innerHTML = '<p>Error al cargar la página. Inténtalo de nuevo.</p>';
                });
        });
    });
});
