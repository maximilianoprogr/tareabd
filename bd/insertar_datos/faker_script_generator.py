import faker
import random
from datetime import datetime, timedelta

# Inicializar Faker para generar datos ficticios
fake = faker.Faker()

# Generar usuarios ficticios
# Cada usuario tiene un RUT único, nombre, email, usuario, contraseña y tipo (Autor, Revisor, Jefe de Comité)


def generate_users():
    users = []
    unique_ruts = set()
    for _ in range(60):
        while True:
            rut = fake.unique.numerify("########-#")
            if rut not in unique_ruts:
                unique_ruts.add(rut)
                break
        nombre = fake.first_name() + ' ' + fake.last_name()
        email = f"{nombre.split()[0].lower()}.{nombre.split()[1].lower()}@{fake.domain_name()}"
        usuario = f"{nombre.split()[0].lower()}{random.randint(10, 99)}"
        password = fake.password(
            length=12, special_chars=True, digits=True, upper_case=True, lower_case=True
        )
        tipo = random.choice(['Autor', 'Revisor', 'Jefe Comite de Programa'])
        users.append((rut, nombre, email, usuario, password, tipo))
    return users

# Generar una lista de tópicos ficticios relacionados con tecnología


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

# Generar artículos ficticios con título, resumen, fecha de envío y estado


def generate_articles(num_articles):
    articles = []
    for _ in range(num_articles):
        titulo = fake.sentence(nb_words=4)
        resumen = fake.text(max_nb_chars=100)
        fecha_envio = fake.date_between(start_date="-1y", end_date="today")
        estado = random.choice(['En revisión', 'Aprobado', 'Rechazado'])
        articles.append((titulo, resumen, fecha_envio, estado))
    return articles

# Asignar tópicos a los artículos de forma aleatoria


def generate_article_topics(articles, topics):
    article_topics = []
    for i, _ in enumerate(articles, start=1):
        topic_id = random.randint(1, len(topics))
        article_topics.append((i, topic_id))
    return article_topics

# Generar evaluaciones ficticias para los artículos


def generate_article_reviews(articles, reviewers):
    reviews = []
    for article_id, _ in enumerate(articles, start=1):
        rut_revisor = random.choice(reviewers)[0]
        resena = fake.sentence(nb_words=10)
        calificacion = random.randint(1, 10)
        reviews.append((article_id, rut_revisor, resena, calificacion))
    return reviews

# Asignar tópicos a los revisores de forma aleatoria


def generate_reviewer_topics(reviewers, topics):
    reviewer_topics = []
    topic_ids = list(range(1, len(topics) + 1))
    for reviewer in reviewers:
        num_topics = random.randint(1, min(3, len(topic_ids)))
        selected_topics = random.sample(topic_ids, num_topics)
        for topic_id in selected_topics:
            reviewer_topics.append((reviewer[0], topic_id))
    return reviewer_topics

# Asignar autores a los artículos de forma aleatoria


def generate_author_articles(articles, authors):
    author_articles = []
    for article_id, _ in enumerate(articles, start=1):
        num_authors = random.randint(1, min(3, len(authors)))
        selected_authors = random.sample(authors, num_authors)
        for i, author in enumerate(selected_authors):
            es_contacto = i == 0
            author_articles.append((article_id, author, int(es_contacto)))
    return author_articles

# Asignar revisores a los artículos basándose en los tópicos asignados


def generate_article_reviewers(articles, reviewers, article_topics, reviewer_topics):
    article_reviewers = []
    articulo_a_topicos = {}
    for id_articulo, id_topico in article_topics:
        articulo_a_topicos.setdefault(id_articulo, set()).add(id_topico)
    revisor_a_topicos = {}
    for rut_revisor, id_topico in reviewer_topics:
        revisor_a_topicos.setdefault(rut_revisor, set()).add(id_topico)
    for article_id, _ in enumerate(articles, start=1):
        posibles_revisores = []
        for reviewer in reviewers:
            rut = reviewer[0]
            if articulo_a_topicos[article_id] & revisor_a_topicos.get(rut, set()):
                posibles_revisores.append(rut)
        if posibles_revisores:
            num_reviewers = min(len(posibles_revisores), random.randint(1, 2))
            seleccionados = random.sample(posibles_revisores, num_reviewers)
            for rut in seleccionados:
                article_reviewers.append((article_id, rut))
    return article_reviewers

# Escapar caracteres especiales en cadenas para SQL


def escape_sql_string(s):
    return s.replace("'", "''")

# Escribir los datos generados en un archivo SQL


def write_to_sql(users, topics, articles, article_topics, reviews, reviewer_topics, author_articles, article_reviewers):
    with open("FAKER_INSERT.sql", "w", encoding="utf-8") as f:
        f.write("SET FOREIGN_KEY_CHECKS = 0;\n")
        f.write("DELETE FROM Articulo_Revisor;\n")
        f.write("DELETE FROM Evaluacion_Articulo;\n")
        f.write("DELETE FROM Articulo_Topico;\n")
        f.write("DELETE FROM Autor_Articulo;\n")
        f.write("DELETE FROM Revisor_Topico;\n")
        f.write("DELETE FROM Articulo;\n")
        f.write("DELETE FROM Revisor;\n")
        f.write("DELETE FROM Autor;\n")
        f.write("DELETE FROM Usuario;\n")
        f.write("DELETE FROM Topico;\n")
        f.write("ALTER TABLE Topico AUTO_INCREMENT = 1;\n")
        f.write("SET FOREIGN_KEY_CHECKS = 1;\n\n")

        # Insertar usuarios
        f.write("-- Inserts para la tabla Usuario\n")
        f.write(
            "INSERT INTO Usuario (rut, nombre, email, usuario, password, tipo) VALUES\n")
        f.write(",\n".join([
            f"('{rut}', '{nombre}', '{email}', '{usuario}', '{password}', '{tipo}')"
            for rut, nombre, email, usuario, password, tipo in users
        ]))
        f.write(";\n\n")

        # Insertar autores
        f.write("-- Inserts para la tabla Autor\n")
        f.write("INSERT INTO Autor (rut) VALUES\n")
        f.write(",\n".join([
            f"('{rut}')"
            for rut, nombre, email, usuario, password, tipo in users if tipo == 'Autor'
        ]))
        f.write(";\n\n")

        # Insertar revisores
        f.write("-- Inserts para la tabla Revisor\n")
        f.write("INSERT INTO Revisor (rut) VALUES\n")
        f.write(",\n".join([
            f"('{rut}')"
            for rut, nombre, email, usuario, password, tipo in users if tipo == 'Revisor'
        ]))
        f.write(";\n\n")

        # Insertar tópicos
        f.write("-- Inserts para la tabla Topico\n")
        f.write("INSERT INTO Topico (nombre) VALUES\n")
        f.write(",\n".join([f"('{topic}')" for topic in topics]))
        f.write(";\n\n")

        # Insertar artículos
        f.write("-- Inserts para la tabla Articulo\n")
        f.write(
            "INSERT INTO Articulo (id_articulo, titulo, resumen, fecha_envio, estado) VALUES\n")
        f.write(",\n".join([
            f"({i+1}, '{escape_sql_string(titulo)}', '{escape_sql_string(resumen)}', '{fecha_envio}', '{estado}')"
            for i, (titulo, resumen, fecha_envio, estado) in enumerate(articles)
        ]))
        f.write(";\n\n")

        # Insertar relaciones artículo-tópico
        f.write("-- Inserts para la tabla Articulo_Topico\n")
        f.write("INSERT INTO Articulo_Topico (id_articulo, id_topico) VALUES\n")
        f.write(",\n".join(
            [f"({id_articulo}, {id_topico})" for id_articulo, id_topico in article_topics]))
        f.write(";\n\n")

        # Insertar evaluaciones de artículos
        f.write("-- Inserts para la tabla Evaluacion_Articulo\n")
        f.write(
            "INSERT INTO Evaluacion_Articulo (id_articulo, rut_revisor, resena, calificacion) VALUES\n")
        f.write(",\n".join([
            f"({id_articulo}, '{rut_revisor}', '{resena}', {calificacion})"
            for id_articulo, rut_revisor, resena, calificacion in reviews
        ]))
        f.write(";\n\n")

        # Insertar relaciones revisor-tópico
        f.write("-- Inserts para la tabla Revisor_Topico\n")
        f.write("INSERT INTO Revisor_Topico (rut_revisor, id_topico) VALUES\n")
        f.write(",\n".join(
            [f"('{rut_revisor}', {id_topico})" for rut_revisor, id_topico in reviewer_topics]))
        f.write(";\n\n")

        # Insertar relaciones autor-artículo
        f.write("-- Inserts para la tabla Autor_Articulo\n")
        f.write(
            "INSERT INTO Autor_Articulo (id_articulo, rut_autor, es_contacto) VALUES\n")
        f.write(",\n".join([
            f"({id_articulo}, '{rut_autor}', {es_contacto})"
            for id_articulo, rut_autor, es_contacto in author_articles
        ]))
        f.write(";\n\n")

        # Insertar relaciones artículo-revisor
        f.write("-- Inserts para la tabla Articulo_Revisor\n")
        f.write("INSERT INTO Articulo_Revisor (id_articulo, rut_revisor) VALUES\n")
        f.write(",\n".join([
            f"({id_articulo}, '{rut_revisor}')"
            for id_articulo, rut_revisor in article_reviewers
        ]))
        f.write(";\n\n")


# Generar y guardar los datos ficticios
if __name__ == "__main__":
    users = generate_users()
    topics = generate_topics()
    authors = [user[0] for user in users if user[5] == 'Autor']
    reviewers = [(user[0], user[1]) for user in users if user[5] == 'Revisor']
    articles = generate_articles(50)
    article_topics = generate_article_topics(articles, topics)
    reviews = generate_article_reviews(articles, reviewers)
    reviewer_topics = generate_reviewer_topics(reviewers, topics)
    author_articles = generate_author_articles(articles, authors)
    article_reviewers = generate_article_reviewers(
        articles, reviewers, article_topics, reviewer_topics)
    write_to_sql(users, topics, articles, article_topics, reviews,
                 reviewer_topics, author_articles, article_reviewers)
    print("Datos generados y guardados en FAKER_INSERT.sql")
