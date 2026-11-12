# Spec Delta

## Purpose

Define la estructura común de las páginas de listado y de ficha de los contenidos del sitio (proyectos, eventos, recursos y territorios), para que sean coherentes y accesibles.

## ADDED Requirements

### Requirement: One main heading per page
Cada página pública SHALL tener exactamente un encabezado de nivel 1, que corresponde al título de la página mostrado en el hero.

#### Scenario: Project page
- **WHEN** un visitante abre la ficha de un proyecto
- **THEN** la página contiene un único `h1` con el título del proyecto

#### Scenario: Archive page
- **WHEN** un visitante abre el archivo de proyectos con 10 proyectos
- **THEN** la página contiene un único `h1` y cada proyecto se presenta con un encabezado de nivel inferior

#### Scenario: Not found page
- **WHEN** un visitante abre una URL que no existe
- **THEN** la página responde 404 y contiene un único `h1` que indica que la página no se ha encontrado

### Requirement: Heading levels do not skip
Dentro del contenido de las plantillas del theme, los encabezados SHALL descender de nivel de uno en uno.

#### Scenario: Project sidebar
- **WHEN** se inspecciona la barra lateral de la ficha de un proyecto
- **THEN** sus títulos ("Endereço", "Território") son de nivel 2

### Requirement: Archives list items as cards
Las páginas de archivo de proyectos, eventos, recursos y de cualquier territorio o categoría SHALL mostrar cada elemento como una tarjeta con título enlazado a su ficha, imagen destacada si existe, contexto y extracto, con paginación cuando haya más elementos que los de una página.

#### Scenario: Project archive
- **WHEN** un visitante abre `/projeto/`
- **THEN** ve tarjetas con título enlazado, modalidade y territorio, y extracto, sin el contenido completo de cada proyecto

#### Scenario: Territory archive
- **WHEN** un visitante abre el archivo de un territorio con proyectos y eventos
- **THEN** ve tarjetas de ambos tipos, cada una enlazando a su ficha

#### Scenario: Archive title
- **WHEN** un visitante abre `/projeto/` o el archivo de un territorio
- **THEN** el título del hero es solo el nombre del archivo (p. ej. "Projetos" o el nombre del territorio), como texto, sin prefijos como "Archives:" ni etiquetas HTML visibles

#### Scenario: Empty archive
- **WHEN** un archivo no tiene elementos
- **THEN** se muestra el mensaje de "nada encontrado"

### Requirement: Every content page has a hero inside the main region
Las fichas de proyecto, evento y recurso y todas las páginas de archivo SHALL mostrar el hero con el título de la página dentro de la región principal del documento.

#### Scenario: Event page
- **WHEN** un visitante abre la ficha de un evento
- **THEN** ve el hero con el título y la imagen destacada del evento, contenido en `<main>`

### Requirement: Comments are not offered on projects
La ficha de proyecto SHALL NOT mostrar formulario ni listado de comentarios.

#### Scenario: Project page
- **WHEN** un visitante abre la ficha de un proyecto
- **THEN** no aparece ninguna sección de comentarios
