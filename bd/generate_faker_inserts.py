import faker
import random
from datetime import datetime, timedelta

# Inicializar Faker
fake = faker.Faker()

# Generar datos para la tabla Usuario


def generate_users():
    users = []
    for _ in range(10):
        rut = fake.unique.numerify("########-#")
        nombre = fake.first_name() + ' ' + fake.last_name()
        email = f"{nombre.split()[0].lower()}.{nombre.split()[1].lower()}@{fake.domain_name()}"
        usuario = f"{nombre.split()[0].lower()}{random.randint(10, 99)}"
        password = fake.password(
            length=12, special_chars=True, digits=True, upper_case=True, lower_case=True)
        tipo = random.choice(['Autor', 'Revisor', 'Jefe Comite de Programa'])
        users.append((rut, nombre, email, usuario, password, tipo))
    return users

# Generar datos para la tabla Autor y Revisor


def generate_authors_and_reviewers(users):
    authors = [user[0] for user in users if user[5] == 'Autor']
    reviewers = [user[0] for user in users if user[5] == 'Revisor']
    return authors, reviewers

# Generar datos para la tabla Topico


def generate_topics():
    topics = [
        'Machine Learning',
        'Cloud Computing',
        'Cybersecurity',
        'Data Science',
        'Blockchain',
        'Artificial Intelligence',
        'Big Data',
        'Internet of Things',
        'Quantum Computing',
        'Augmented Reality'
    ]
    return topics

# Generar datos para la tabla Articulo


def generate_articles(authors):
    articles = []
    for _ in range(50):  # Cambiado a 50 artículos
        titulo = fake.sentence(nb_words=4)
        resumen = fake.text(max_nb_chars=100)
        fecha_envio = fake.date_between(start_date="-1y", end_date="today")
        rut_autor = random.choice(authors)
        estado = random.choice(['En revisión', 'Aprobado', 'Rechazado'])
        articles.append((titulo, resumen, fecha_envio, rut_autor, estado))
    return articles

# Generar datos para la tabla Articulo_Topico


def generate_article_topics(articles, topics):
    article_topics = []
    for i, article in enumerate(articles, start=1):
        topic_id = random.randint(1, len(topics))
        article_topics.append((i, topic_id))
    return article_topics

# Generar datos para la tabla Evaluacion_Articulo


def generate_article_reviews(articles, reviewers):
    reviews = []
    for article_id, article in enumerate(articles, start=1):
        rut_revisor = random.choice(reviewers)
        resena = fake.sentence(nb_words=10)
        calificacion = random.randint(1, 10)
        reviews.append((article_id, rut_revisor, resena, calificacion))
    return reviews

# Generar datos para la tabla Revisor_Topico


def generate_reviewer_topics(reviewers, topics):
    reviewer_topics = []
    for reviewer in reviewers:
        topic_id = random.randint(1, len(topics))
        reviewer_topics.append((reviewer, topic_id))
    return reviewer_topics

# Generar datos para la tabla Autor_Articulo (relación entre autores y artículos con contacto principal)


def generate_author_articles(articles, authors):
    author_articles = []
    for article_id, article in enumerate(articles, start=1):
        # Cada artículo puede tener entre 1 y 3 autores
        num_authors = random.randint(1, 3)
        selected_authors = random.sample(authors, num_authors)
        for i, author in enumerate(selected_authors):
            es_contacto = i == 0  # El primer autor seleccionado será el contacto principal
            author_articles.append((article_id, author, es_contacto))
    return author_articles

# Escribir los datos generados en un archivo SQL


def write_to_sql(users, authors, reviewers, topics, articles, article_topics, reviews, reviewer_topics, author_articles):
    with open("FAKER_INSERT.sql", "w", encoding="utf-8") as f:
        f.write(
            "-- Este archivo contiene datos generados automáticamente para pruebas\n")

        # Usuarios
        f.write("-- Inserts para la tabla Usuario\n")
        f.write(
            "INSERT INTO Usuario (rut, nombre, email, usuario, password, tipo) VALUES\n")
        f.write(",\n".join([
            f"('{rut}', '{nombre}', '{email}', '{usuario}', '{password}', '{tipo}')"
            for rut, nombre, email, usuario, password, tipo in users
        ]))
        f.write(";\n\n")

        # Autores
        f.write("-- Inserts para la tabla Autor\n")
        f.write("INSERT INTO Autor (rut) VALUES\n")
        f.write(",\n".join([f"('{rut}')" for rut in authors]))
        f.write(";\n\n")

        # Revisores
        f.write("-- Inserts para la tabla Revisor\n")
        f.write("INSERT INTO Revisor (rut) VALUES\n")
        f.write(",\n".join([f"('{rut}')" for rut in reviewers]))
        f.write(";\n\n")

        # Tópicos
        f.write("-- Inserts para la tabla Topico\n")
        f.write("INSERT INTO Topico (nombre) VALUES\n")
        f.write(",\n".join([f"('{topic}')" for topic in topics]))
        f.write(";\n\n")

        # Artículos
        f.write("-- Inserts para la tabla Articulo\n")
        f.write(
            "INSERT INTO Articulo (titulo, resumen, fecha_envio, rut_autor, estado) VALUES\n")
        f.write(",\n".join([
            f"('{titulo}', '{resumen}', '{fecha_envio}', '{rut_autor}', '{estado}')"
            for titulo, resumen, fecha_envio, rut_autor, estado in articles
        ]))
        f.write(";\n\n")

        # Artículo-Tópico
        f.write("-- Inserts para la tabla Articulo_Topico\n")
        f.write("INSERT INTO Articulo_Topico (id_articulo, id_topico) VALUES\n")
        f.write(",\n".join(
            [f"({id_articulo}, {id_topico})" for id_articulo, id_topico in article_topics]))
        f.write(";\n\n")

        # Evaluaciones de Artículos
        f.write("-- Inserts para la tabla Evaluacion_Articulo\n")
        f.write(
            "INSERT INTO Evaluacion_Articulo (id_articulo, rut_revisor, resena, calificacion) VALUES\n")
        f.write(",\n".join([
            f"({id_articulo}, '{rut_revisor}', '{resena}', {calificacion})"
            for id_articulo, rut_revisor, resena, calificacion in reviews
        ]))
        f.write(";\n\n")

        # Revisor-Tópico
        f.write("-- Inserts para la tabla Revisor_Topico\n")
        f.write("INSERT INTO Revisor_Topico (rut_revisor, id_topico) VALUES\n")
        f.write(",\n".join(
            [f"('{rut_revisor}', {id_topico})" for rut_revisor, id_topico in reviewer_topics]))
        f.write(";\n")

        # Autor-Artículo
        f.write("-- Inserts para la tabla Autor_Articulo\n")
        f.write(
            "INSERT INTO Autor_Articulo (id_articulo, rut_autor, es_contacto) VALUES\n")
        f.write(",\n".join([
            f"({id_articulo}, '{rut_autor}', {es_contacto})"
            for id_articulo, rut_autor, es_contacto in author_articles
        ]))
        f.write(";\n")


if __name__ == "__main__":
    users = generate_users()
    authors, reviewers = generate_authors_and_reviewers(users)
    topics = generate_topics()
    articles = generate_articles(authors)
    article_topics = generate_article_topics(articles, topics)
    reviews = generate_article_reviews(articles, reviewers)
    reviewer_topics = generate_reviewer_topics(reviewers, topics)
    author_articles = generate_author_articles(articles, authors)
    write_to_sql(users, authors, reviewers, topics, articles,
                 article_topics, reviews, reviewer_topics, author_articles)
    print("Datos generados y guardados en FAKER_INSERT.sql")
