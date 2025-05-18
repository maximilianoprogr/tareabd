document.getElementById('form-evaluacion').addEventListener('submit', function (e) {
    e.preventDefault(); // Evitar redirección

    // Validar campos requeridos
    const calidadTecnica = document.getElementById('calidad_tecnica').checked;
    const originalidad = document.getElementById('originalidad').checked;
    const valoracionGlobal = document.getElementById('valoracion_global').checked;
    const argumentosValoracion = document.getElementById('argumentos_valoracion').value.trim();
    const comentariosAutores = document.getElementById('comentarios_autores').value.trim();

    if (!calidadTecnica && !originalidad && !valoracionGlobal) {
        alert('Debe seleccionar al menos una opción de evaluación.');
        return;
    }

    if (argumentosValoracion === '') {
        alert('Debe completar los argumentos de valoración global.');
        return;
    }

    if (comentariosAutores === '') {
        alert('Debe completar los comentarios a los autores.');
        return;
    }

    const formData = new FormData(this);

    fetch('procesar_evaluacion.php?revision=12345678-9', {
        method: 'POST',
        body: formData
    })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                alert(data.message);
            } else {
                alert('Error: ' + data.message);
            }
        })
        .catch(error => {
            console.error('Error:', error);
            alert('Ocurrió un error al enviar la evaluación.');
        });
});
