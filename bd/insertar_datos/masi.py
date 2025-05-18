import faker
import random
from datetime import datetime, timedelta

# Inicializar Faker
fake = faker.Faker()

# Generar datos para la tabla Usuario


def generate_users():
    users = []
    unique_ruts = set()
    for _ in range(10):
        while True:
            rut = fake.unique.numerify("########-#")
            if rut not in unique_ruts:
                unique_ruts.add(rut)
                break
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
    reviewers = [(user[0], user[1]) for user in users if user[5] == 'Revisor']
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
        rut_revisor = random.choice(reviewers)[0]
        resena = fake.sentence(nb_words=10)
        calificacion = random.randint(1, 10)
        reviews.append((article_id, rut_revisor, resena, calificacion))
    return reviews

# Generar datos para la tabla Revisor_Topico


def generate_reviewer_topics(reviewers, topics):
    reviewer_topics = []
    topic_ids = list(range(1, len(topics) + 1))
    for reviewer in reviewers:
        # Cada revisor tendrá al menos 1 especialidad, pero puede tener más
        num_topics = random.randint(1, min(3, len(topic_ids)))  # 1 a 3 tópicos por revisor
        selected_topics = random.sample(topic_ids, num_topics)
        for topic_id in selected_topics:
            reviewer_topics.append((reviewer[0], topic_id))
    return reviewer_topics

# Generar datos para la tabla Autor_Articulo (relación entre autores y artículos con contacto principal)


def generate_author_articles(articles, authors):
    author_articles = []
    for article_id, article in enumerate(articles, start=1):
        # Cada artículo debe tener al menos un autor
        num_authors = random.randint(1, min(3, len(authors)))
        selected_authors = random.sample(authors, num_authors)
        for i, author in enumerate(selected_authors):
            es_contacto = i == 0  # El primer autor seleccionado será el contacto principal
            author_articles.append((article_id, author, int(es_contacto)))
    return author_articles

# Generar datos para la tabla Articulo_Revisor


def generate_article_reviewers(articles, reviewers, article_topics, reviewer_topics):
    article_reviewers = []
    # Crear un diccionario: artículo -> set de tópicos
    articulo_a_topicos = {}
    for id_articulo, id_topico in article_topics:
        articulo_a_topicos.setdefault(id_articulo, set()).add(id_topico)
    # Crear un diccionario: rut_revisor -> set de tópicos
    revisor_a_topicos = {}
    for rut_revisor, id_topico in reviewer_topics:
        revisor_a_topicos.setdefault(rut_revisor, set()).add(id_topico)
    for article_id, article in enumerate(articles, start=1):
        posibles_revisores = []
        for reviewer in reviewers:
            rut = reviewer[0]
            if articulo_a_topicos[article_id] & revisor_a_topicos.get(rut, set()):
                posibles_revisores.append(rut)
        # Si hay posibles revisores, asignar 1 o 2 distintos
        if posibles_revisores:
            num_reviewers = min(len(posibles_revisores), random.randint(1, 2))
            seleccionados = random.sample(posibles_revisores, num_reviewers)
            for rut in seleccionados:
                article_reviewers.append((article_id, rut))
        # Si no hay ningún revisor con especialidad, no asigna ninguno
    return article_reviewers

# Escribir los datos generados en un archivo SQL


def write_to_sql(users, topics, articles, article_topics, reviews, reviewer_topics, author_articles, article_reviewers):
    with open("FAKER_INSERT.sql", "w", encoding="utf-8") as f:
        # Deshabilitar restricciones de claves foráneas
        f.write("SET FOREIGN_KEY_CHECKS = 0;\n")
        f.write("DELETE FROM Articulo_Revisor;\n")
        f.write("DELETE FROM Revisor_Topico;\n")
        f.write("DELETE FROM Revisor;\n")
        f.write("DELETE FROM Autor_Articulo;\n")
        f.write("DELETE FROM Autor;\n")
        f.write("DELETE FROM Usuario;\n\n")
        f.write("SET FOREIGN_KEY_CHECKS = 1;\n\n")

        # Usuarios
        f.write("-- Inserts para la tabla Usuario\n")
        f.write("INSERT INTO Usuario (rut, nombre, email, usuario, password, tipo) VALUES\n")
        f.write(",\n".join([
            f"('{rut}', '{nombre}', '{email}', '{usuario}', '{password}', '{tipo}')"
            for rut, nombre, email, usuario, password, tipo in users
        ]))
        f.write(";\n\n")

        # Autores
        f.write("-- Inserts para la tabla Autor\n")
        f.write("INSERT INTO Autor (rut) VALUES\n")
        f.write(",\n".join([
            f"('{rut}')"
            for rut, nombre, email, usuario, password, tipo in users if tipo == 'Autor'
        ]))
        f.write(";\n\n")

        # Revisores
        f.write("-- Inserts para la tabla Revisor\n")
        f.write("INSERT INTO Revisor (rut) VALUES\n")
        f.write(",\n".join([
            f"('{rut}')"
            for rut, nombre, email, usuario, password, tipo in users if tipo == 'Revisor'
        ]))
        f.write(";\n\n")

        # Tópicos
        f.write("-- Inserts para la tabla Topico\n")
        f.write("INSERT INTO Topico (nombre) VALUES\n")
        f.write(",\n".join([f"('{topic}')" for topic in topics]))
        f.write(";\n\n")

        # Artículos
        f.write("-- Inserts para la tabla Articulo\n")
        f.write("INSERT INTO Articulo (titulo, resumen, fecha_envio, rut_autor, estado) VALUES\n")
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
        f.write("INSERT INTO Evaluacion_Articulo (id_articulo, rut_revisor, resena, calificacion) VALUES\n")
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
        f.write(";\n\n")

        # Autor-Artículo
        f.write("-- Inserts para la tabla Autor_Articulo\n")
        f.write("INSERT INTO Autor_Articulo (id_articulo, rut_autor, es_contacto) VALUES\n")
        f.write(",\n".join([
            f"({id_articulo}, '{rut_autor}', {es_contacto})"
            for id_articulo, rut_autor, es_contacto in author_articles
        ]))
        f.write(";\n\n")

        # Artículo-Revisor
        f.write("-- Inserts para la tabla Articulo_Revisor\n")
        f.write("INSERT INTO Articulo_Revisor (id_articulo, rut_revisor) VALUES\n")
        f.write(",\n".join([
            f"({id_articulo}, '{rut_revisor}')"
            for id_articulo, rut_revisor in article_reviewers
        ]))
        f.write(";\n\n")


# --- En el main, solo necesitas users, no separar autores/revisores ---
if __name__ == "__main__":
    users = generate_users()
    topics = generate_topics()
    # Extraer autores y revisores desde users
    authors = [user[0] for user in users if user[5] == 'Autor']
    reviewers = [(user[0], user[1]) for user in users if user[5] == 'Revisor']
    articles = generate_articles(authors)
    article_topics = generate_article_topics(articles, topics)
    reviews = generate_article_reviews(articles, reviewers)
    reviewer_topics = generate_reviewer_topics(reviewers, topics)
    author_articles = generate_author_articles(articles, authors)
    article_reviewers = generate_article_reviewers(articles, reviewers, article_topics, reviewer_topics)
    write_to_sql(users, topics, articles, article_topics, reviews, reviewer_topics, author_articles, article_reviewers)
    print("Datos generados y guardados en FAKER_INSERT.sql")
