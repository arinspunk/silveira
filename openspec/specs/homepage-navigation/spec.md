# homepage-navigation Specification

## Purpose

Define la interacción de la portada con tres estados (contraído con hero, mapa expandido y footer revelado) y cómo se pasa entre ellos con ratón, táctil y teclado, de forma accesible.

## Requirements

### Requirement: Map expands from pointer, touch and keyboard
Desde el estado contraído, el sistema SHALL pasar a mapa expandido cuando se desplaza hacia abajo con rueda o gesto, se pulsa un filtro o un marcador, el foco entra en los filtros o en el mapa, o se pulsa `PageDown`, `Space` o flecha abajo fuera de un campo de formulario.

#### Scenario: Keyboard user tabs into filters
- **WHEN** con la portada contraída una persona usuaria de teclado tabula hasta el primer filtro
- **THEN** el mapa se expande y el filtro enfocado permanece visible y enfocado

#### Scenario: PageDown
- **WHEN** con la portada contraída y el foco en el documento se pulsa `PageDown`
- **THEN** el mapa se expande

#### Scenario: Wheel (existing behaviour)
- **WHEN** con la portada contraída se gira la rueda hacia abajo
- **THEN** el mapa se expande

### Requirement: Map collapses back to the hero
Desde mapa expandido, el sistema SHALL volver al estado contraído al desplazar hacia arriba con rueda o gesto sobre la barra de filtros o la cabecera, o al pulsar `PageUp` u `Home` fuera de un campo de formulario y fuera del mapa.

#### Scenario: Home key
- **WHEN** con el mapa expandido y el foco en un filtro se pulsa `Home`
- **THEN** la portada vuelve al estado contraído

### Requirement: Footer is reachable by every input method
Desde mapa expandido, el sistema SHALL revelar el footer al pasar el ratón por su zona visible, al tocar o deslizar hacia arriba sobre la franja visible del footer en táctil, al entrar el foco en cualquiera de sus elementos, o al pulsar `End`.

#### Scenario: Touch device
- **WHEN** en un móvil con el mapa expandido se toca la franja visible del footer
- **THEN** el footer se revela completo y sus enlaces se pueden pulsar

#### Scenario: Keyboard focus enters footer
- **WHEN** una persona usuaria de teclado tabula desde el último elemento del mapa hacia el primer enlace del footer
- **THEN** el footer se revela y el enlace enfocado es visible

#### Scenario: Footer closes
- **WHEN** el footer está revelado y el foco sale de él hacia arriba, se pulsa `PageUp`, o se desplaza hacia arriba con rueda o gesto
- **THEN** el footer se oculta y la portada vuelve a mapa expandido

### Requirement: Focused elements are always visible
El sistema SHALL garantizar que el elemento con el foco nunca queda fuera de la zona visible ni tapado por el estado actual de la portada.

#### Scenario: Shift+Tab from map to header
- **WHEN** con el mapa expandido se tabula hacia atrás hasta un enlace de la cabecera
- **THEN** el enlace es visible y enfocado

### Requirement: Hidden project list is not focusable
Cuando el panel de la lista de proyectos está cerrado, sus elementos SHALL NOT poder recibir el foco ni ser anunciados por lectores de pantalla; el botón que lo abre SHALL exponer si el panel está abierto o cerrado.

#### Scenario: Tabbing with closed panel
- **WHEN** el panel de la lista está cerrado y se tabula por la portada
- **THEN** el foco nunca entra en elementos de la lista

#### Scenario: Toggle state announced
- **WHEN** un lector de pantalla enfoca el botón de la lista tras abrirla
- **THEN** anuncia que está expandido

### Requirement: Reduced motion
Si la persona usuaria tiene activada la preferencia de movimiento reducido, los cambios entre estados SHALL ser inmediatos y la portada SHALL aceptar la siguiente acción sin periodo de espera.

#### Scenario: Rapid keyboard navigation with reduced motion
- **WHEN** con movimiento reducido activo se pulsa `PageDown` y a continuación `End`
- **THEN** el mapa se expande y el footer se revela de inmediato, sin ignorar la segunda tecla

### Requirement: Works without JavaScript
Si el JavaScript de la portada no se carga, todo el contenido de la portada (hero, filtros, mapa y footer) SHALL ser alcanzable con el scroll nativo del navegador.

#### Scenario: Script blocked
- **WHEN** se carga la portada con JavaScript desactivado
- **THEN** se puede desplazar la página hasta el footer y sus enlaces funcionan
