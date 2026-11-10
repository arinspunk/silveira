# project-map Specification

## Purpose

Mapa interactivo de la portada que muestra los proyectos publicados con coordenadas, con un popup por marcador y una lista lateral, presentando sus datos de forma segura y fiel al contenido editado.

## Requirements

### Requirement: Project content is displayed as text
El mapa SHALL mostrar el título, lema, extracto y texto de contexto (modalidade/territorio) de cada proyecto como texto plano en el popup y en la lista lateral: el marcado HTML contenido en esos campos se elimina y nunca se interpreta.

#### Scenario: Title containing HTML markup
- **WHEN** un proyecto publicado tiene como título `<img src=x onerror=alert(1)>Horta`
- **THEN** el popup y la lista muestran `Horta` (sin la etiqueta), no se crea ningún elemento `<img>` a partir de él y no se ejecuta ningún script

#### Scenario: Lema containing a script terminator
- **WHEN** el lema de un proyecto contiene `</script><script>alert(1)</script>`
- **THEN** la portada carga sin errores, el mapa muestra todos los proyectos con coordenadas, no se ejecuta ningún script y el lema se muestra sin las etiquetas (vacío si solo contenía `<script>`)

### Requirement: Typographic characters render correctly
El mapa SHALL mostrar apóstrofos, comillas, ampersands y caracteres acentuados tal como los ve el lector en la ficha del proyecto, nunca como entidades HTML visibles.

#### Scenario: Title with apostrophe and ampersand
- **WHEN** un proyecto se titula `A Horta d'Ana & Cía`
- **THEN** el popup y la lista muestran `A Horta d’Ana & Cía` (o con apóstrofo recto), sin secuencias como `&#8217;` o `&amp;`

### Requirement: Only safe URLs are used for links and images
Los enlaces "Ver projeto" y los elementos de la lista SHALL apuntar únicamente a URLs `http` o `https`, y la imagen del popup SHALL cargarse solo desde URLs `http` o `https`; si una URL no cumple, el enlace o la imagen se omite.

#### Scenario: Valid permalink and thumbnail
- **WHEN** un proyecto tiene permalink e imagen destacada normales
- **THEN** el popup muestra la imagen y el enlace "Ver projeto" lleva a la ficha del proyecto

#### Scenario: Unsafe URL scheme
- **WHEN** la URL de un punto empieza por `javascript:` o `data:`
- **THEN** no se renderiza ningún enlace ni imagen con esa URL

#### Scenario: Project without featured image
- **WHEN** un proyecto no tiene imagen destacada
- **THEN** el popup se muestra sin bloque de imagen y sin imágenes rotas

### Requirement: Image alternative text
La imagen del popup SHALL tener como texto alternativo el título del proyecto en texto plano.

#### Scenario: Alt text with special characters
- **WHEN** el título del proyecto contiene comillas dobles
- **THEN** el atributo `alt` de la imagen contiene el título completo sin romper el atributo ni añadir atributos extra

### Requirement: Selecting a marker expands the map
Al pulsar un marcador mientras el mapa está en estado contraído, el sistema SHALL expandir el mapa y abrir el popup del proyecto, también en el build de producción.

#### Scenario: Marker click in production build
- **WHEN** con assets compilados (sin servidor de desarrollo de Vite) un visitante pulsa un marcador con el mapa contraído
- **THEN** el mapa pasa a estado expandido, aparece el botón "Ver a lista" y se muestra el popup del proyecto
