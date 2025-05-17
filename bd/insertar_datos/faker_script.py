from faker import Faker
import random
from datetime import date, timedelta

fake = Faker('es_ES')
output_file = 'big_dataset.sql'

def generar_rut():
    while True:
        num = fake.random_number(digits=8, fix_len=True)
        verificador = random.randint(1,9)
        rut = f"{num}-{verificador}"
        if rut not in ruts_generados:
            ruts_generados.add(rut)
            return rut

ruts_generados = set()

usuarios = {'autores': [], 'revisores': [], 'jefe': None}
articulos = []
topicos = [
    'Inteligencia Artificial', 'Machine Learning', 'Deep Learning',
    'Redes Neuronales', 'Procesamiento Lenguaje Natural',
    'Visión por Computadora', 'Robótica Autónoma', 'Big Data Analytics',
    'Computación en la Nube', 'Edge Computing', 'IoT Industrial',
    'Blockchain Empresarial', 'Ciberseguridad Ofensiva',
    'Ethical Hacking', 'DevOps Avanzado', 'Arquitecturas Microservicios',
    'Realidad Virtual', 'Realidad Aumentada', 'Computación Cuántica',
    'Bioinformática', 'Smart Cities', 'Vehiculos Autónomos',
    'Sistemas Recomendación', 'Fintech', 'Agricultura de Precisión'
]

with open(output_file, 'w', encoding='utf-8') as f:
    # Generar Usuarios
    f.write("-- ========== USUARIOS ==========\n")
    
    # 50 Autores
    f.write("\n-- Autores\n")
    for _ in range(50):
        rut = generar_rut()
        usuarios['autores'].append(rut)
        f.write(f"INSERT INTO Usuario VALUES ('{rut}', '{fake.name()}', '{fake.unique.email()}', "
                f"'{fake.user_name()}', '{fake.password(length=12)}', 'Autor');\n")
    
    # 50 Revisores
    f.write("\n-- Revisores\n")
    for _ in range(50):
        rut = generar_rut()
        usuarios['revisores'].append(rut)
        f.write(f"INSERT INTO Usuario VALUES ('{rut}', '{fake.name()}', '{fake.unique.email()}', "
                f"'{fake.user_name()}', '{fake.password(length=12)}', 'Revisor');\n")
    
    # 1 Jefe
    f.write("\n-- Jefe\n")
    rut = generar_rut()
    usuarios['jefe'] = rut
    f.write(f"INSERT INTO Usuario VALUES ('{rut}', '{fake.name()}', '{fake.unique.email()}', "
            f"'{fake.user_name()}', '{fake.password(length=12)}', 'Jefe Comite de Programa');\n")

    # Insertar Autores y Revisores
    f.write("\n-- Registros Autor\n")
    for rut in usuarios['autores']:
        f.write(f"INSERT INTO Autor VALUES ('{rut}');\n")
    
    f.write("\n-- Registros Revisor\n")
    for rut in usuarios['revisores']:
        f.write(f"INSERT INTO Revisor VALUES ('{rut}');\n")

    # Insertar Tópicos
    f.write("\n-- Tópicos\n")
    for topico in topicos:
        f.write(f"INSERT INTO Topico (nombre) VALUES ('{topico}');\n")

    # ... (código anterior)

    # Generar Artículos (400+)
    f.write("\n-- Artículos\n")
    articulo_id = 1
    total_articulos = 400
    articulos_por_autor = total_articulos // len(usuarios['autores']) + 1
    
    for autor in usuarios['autores']:
        for _ in range(articulos_por_autor):
            if articulo_id > total_articulos:
                break
            
            titulo = fake.sentence(nb_words=8)[:-1].replace("'", "''")
            resumen = fake.text(max_nb_chars=250).replace("'", "''")
            fecha = fake.date_between(start_date='-3y', end_date='today')
            estado = random.choices(
                population=['En revisión', 'Aprobado', 'Rechazado'],
                weights=[0.4, 0.4, 0.2],
                k=1
            )[0]
            
            f.write(f"INSERT INTO Articulo (titulo, resumen, fecha_envio, rut_autor, estado) VALUES ("
                    f"'{titulo}', '{resumen}', '{fecha}', '{autor}', '{estado}');\n")
            
            # Coautores (0-3) - LÍNEA CORREGIDA
            coautores = random.sample(
                [a for a in usuarios['autores'] if a != autor],
                k=random.randint(0, 3)
            )  # Paréntesis faltante añadido aquí
            
            for coautor in coautores:
                f.write(f"INSERT INTO Autor_Articulo VALUES ({articulo_id}, '{coautor}');\n")
            
            # Tópicos (2-4)
            arts_topicos = random.sample(range(1, len(topicos)+1), random.randint(2,4))
            for topico in arts_topicos:
                f.write(f"INSERT INTO Articulo_Topico VALUES ({articulo_id}, {topico});\n")
            
            articulo_id += 1


    # Asignar tópicos a revisores (3-5 tópicos por revisor)
    f.write("\n-- Experticia Revisores\n")
    for revisor in usuarios['revisores']:
        revisores_topicos = random.sample(
            range(1, len(topicos)+1),
            k=random.randint(3,5)
        )
        for topico in revisores_topicos:
            f.write(f"INSERT INTO Revisor_Topico VALUES ('{revisor}', {topico});\n")

    # Asignar revisores a artículos y crear evaluaciones
    f.write("\n-- Revisiones y Evaluaciones\n")
    for art_id in range(1, articulo_id):
        # Obtener tópicos del artículo (simulado)
        topicos_art = random.sample(range(1, len(topicos)+1), random.randint(2,4))
        
        # Buscar revisores con al menos 1 tópico en común
        revisores_candidatos = []
        for revisor in usuarios['revisores']:
            cursor.execute(f"SELECT id_topico FROM Revisor_Topico WHERE rut_revisor = '{revisor}'")
            topicos_revisor = [row[0] for row in cursor.fetchall()]
            if set(topicos_art) & set(topicos_revisor):
                revisores_candidatos.append(revisor)
        
        # Seleccionar 2-3 revisores válidos
        num_revisores = min(3, max(2, len(revisores_candidatos)))
        if num_revisores < 2:
            revisores_asignados = random.sample(usuarios['revisores'], k=2)
        else:
            revisores_asignados = random.sample(revisores_candidatos, k=num_revisores)
        
        for revisor in revisores_asignados:
            resena = fake.paragraph(nb_sentences=3).replace("'", "''")
            calificacion = random.choices(
                population=[1, 2, 3, 4, 5],
                weights=[0.1, 0.2, 0.3, 0.3, 0.1],
                k=1
            )[0]
            
            f.write(f"INSERT INTO Articulo_Revisor VALUES ({art_id}, '{revisor}');\n")
            f.write(f"INSERT INTO Evaluacion_Articulo VALUES ("
                    f"{art_id}, '{revisor}', '{resena}', {calificacion});\n")

print(f"Archivo SQL generado: {output_file}")