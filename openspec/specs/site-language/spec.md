# site-language Specification

## Purpose

Fija la norma lingüística de los textos que el theme muestra en el sitio público y en el admin (galego reintegrado, variante "umha"/"-çom"/"nom") y que todos ellos sean traducibles.

## Requirements

### Requirement: Public interface text uses the site language norm
Todo texto de interfaz que el theme muestra en el sitio público SHALL estar en galego reintegrado, variante "umha"/"-çom"/"nom", sin textos en castellano, inglés o portugués estándar.

#### Scenario: Not found page
- **WHEN** un visitante llega a una búsqueda sin resultados
- **THEN** el mensaje y el formulario de búsqueda se muestran en galego reintegrado, sin formas como "não", "pueda" o "con palavras-chave"

#### Scenario: Map list toggle
- **WHEN** un visitante abre y cierra la lista lateral del mapa
- **THEN** los textos del botón y de "ver o projeto" están en galego reintegrado

#### Scenario: Navigation labels
- **WHEN** un lector de pantalla anuncia las regiones de navegación de cabecera y footer
- **THEN** sus nombres accesibles están en galego reintegrado

### Requirement: Admin labels defined by the theme use the site language norm
Las etiquetas de los tipos de contenido, taxonomías, ubicaciones de menú y avisos de administración definidos por el theme SHALL estar en galego reintegrado, variante "umha"/"-çom"/"nom".

#### Scenario: Empty trash message
- **WHEN** una persona editora abre la papelera de proyectos vacía
- **THEN** el mensaje usa la variante del sitio (p. ej. con "Nom"), no "Não encontrado na lixeira"

### Requirement: Theme text is translatable
Todo texto de interfaz del theme, incluido el que muestra JavaScript, SHALL obtenerse mediante las funciones de traducción de WordPress con el dominio `silveira`.

#### Scenario: Translation file overrides a string
- **WHEN** se instala un fichero de traducción del dominio `silveira` para el idioma del sitio que cambia el texto del botón de la lista del mapa
- **THEN** el mapa muestra el texto traducido

### Requirement: Archive titles have no generic prefix
Los títulos de las páginas de archivo SHALL mostrar solo el nombre del tipo de contenido, territorio o categoría, sin prefijos como "Arquivo:", "Archivo:" o "Categoria:", en cualquier idioma configurado en WordPress.

#### Scenario: Territory archive
- **WHEN** un visitante abre el archivo del territorio "Deza"
- **THEN** el título del hero es "Deza"

### Requirement: Search terms are shown verbatim
El título de resultados de búsqueda SHALL mostrar el término buscado tal como lo escribió el visitante, escapado una sola vez.

#### Scenario: Search with ampersand
- **WHEN** se busca "horta & mar"
- **THEN** el título muestra `horta & mar`, no `horta &amp; mar`
