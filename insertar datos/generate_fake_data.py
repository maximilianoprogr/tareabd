import faker 
import random
from datetime import datetime

# Crear una instancia de Faker
fake = faker.Faker()

# Generar datos para la tabla Usuario


def generate_usuarios(n):
    usuarios = []
    for _ in range(n):
        rut = fake.unique.bothify(text='#########?')  # Ejemplo: 123456789K
        nombre = fake.name()
        email = fake.unique.email()
        usuario = fake.user_name()
        password = fake.password()
        tipo = random.choice(['Autor', 'Revisor'])
        usuarios.append((rut, nombre, email, usuario, password, tipo))
    return usuarios

# Generar datos para la tabla Autor


def generate_autores(usuarios):
    return [(u[0],) for u in usuarios if u[5] == 'Autor']

# Generar datos para la tabla Revisor


def generate_revisores(usuarios):
    return [(u[0],) for u in usuarios if u[5] == 'Revisor']

# Generar datos para la tabla Articulo


def generate_articulos(autores, n):
    articulos = []
    for _ in range(n):
        id_articulo = fake.unique.random_int(min=1, max=1000)
        titulo = fake.sentence(nb_words=5)
        resumen = fake.text(max_nb_chars=200)
        fecha_envio = fake.date_between(start_date='-2y', end_date='today')
        rut_autor = random.choice(autores)[0]
        estado = random.choice(['En revisión', 'Aprobado', 'Rechazado'])
        articulos.append((id_articulo, titulo, resumen,
                         fecha_envio, rut_autor, estado))
    return articulos

# Generar datos para la tabla Topico


def generate_topicos(n):
    topicos = []
    for _ in range(n):
        id_topico = fake.unique.random_int(min=1, max=100)
        nombre = fake.word()
        topicos.append((id_topico, nombre))
    return topicos

# Generar datos para la tabla Articulo_Topico


def generate_articulo_topico(articulos, topicos):
    articulo_topico = []
    for articulo in articulos:
        id_articulo = articulo[0]
        id_topico = random.choice(topicos)[0]
        articulo_topico.append((id_articulo, id_topico))
    return articulo_topico

# Generar datos para la tabla Evaluacion_Articulo


def generate_evaluacion_articulo(articulos, revisores):
    evaluaciones = []
    for articulo in articulos:
        id_articulo = articulo[0]
        rut_revisor = random.choice(revisores)[0]
        resena = fake.text(max_nb_chars=100)
        calificacion = random.randint(1, 5)
        evaluaciones.append((id_articulo, rut_revisor, resena, calificacion))
    return evaluaciones


# Agregar los resultados generados a un archivo
if __name__ == "__main__":
    # Generar datos
    usuarios = generate_usuarios(10)
    autores = generate_autores(usuarios)
    revisores = generate_revisores(usuarios)
    articulos = generate_articulos(autores, 5)
    topicos = generate_topicos(3)
    articulo_topico = generate_articulo_topico(articulos, topicos)
    evaluaciones = generate_evaluacion_articulo(articulos, revisores)

    # Guardar datos generados en un archivo
    with open("generated_data.txt", "w", encoding="utf-8") as file:
        file.write("Usuarios:\n" + str(usuarios) + "\n\n")
        file.write("Autores:\n" + str(autores) + "\n\n")
        file.write("Revisores:\n" + str(revisores) + "\n\n")
        file.write("Articulos:\n" + str(articulos) + "\n\n")
        file.write("Topicos:\n" + str(topicos) + "\n\n")
        file.write("Articulo_Topico:\n" + str(articulo_topico) + "\n\n")
        file.write("Evaluacion_Articulo:\n" + str(evaluaciones) + "\n\n")

    print("Datos generados y guardados en 'generated_data.txt'")
