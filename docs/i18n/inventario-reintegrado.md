# Theme text inventory for silveira — galego reintegrado ("umha"/"-çom"/"nom")

To regenerate it: `wp i18n make-pot wp-content/themes/silveira /tmp/silveira.pot --domain=silveira --exclude=vendor,node_modules,assets,public,template-kitchen-sink.php`.

Generated with `wp i18n make-pot` (`silveira` domain, without vendor/node_modules/assets or the Kitchen Sink) when applying the `i18n-reintegrado` change. The site texts in the table are quoted exactly as they appear in the theme (galego reintegrado); only this explanation, the column headers and the notes are in English.

**For the linguistic review (later and non-blocking):** the texts marked with ★ are specific doubts; the rest can be reviewed as well. Corrections are applied in a follow-up change.

**General doubt ★:** the admin labels use title case ("Adicionar Novo Projeto", "Todos os Eventos"); the Galician norm would capitalize only the first letter ("Adicionar novo projeto"). Also "Adicionar" / "Engadir" / "Acrescentar" and "Buscar" / "Procurar".

Total: 111 texts.

| ★ | Text | Context | File | Change in this change | Note |
|---|---|---|---|---|---|
|  | Silveira |  | style.css |  |  |
|  | http://silveira.localhost |  | style.css |  |  |
|  | Tema de desenvolvimento para o projeto silveira. |  | style.css | before: Theme de desarrollo para el proyecto silveira. |  |
| ★ | Mostrar o rodapé |  | footer.php:13 |  | "rodapé" / "pé de página" |
|  | Umha sebe coidada com agarimo pola %s |  | footer.php:28 |  |  |
|  | Semente |  | footer.php:29 |  |  |
|  | Navegaçom do rodapé |  | footer.php:46 | before: Footer navigation |  |
|  | Modalidade educativa |  | front-page.php:22, front-page.php:26 |  |  |
|  | Que procuras? |  | front-page.php:23 |  |  |
|  | Comarca |  | front-page.php:53, front-page.php:57, inc/tax-shared.php:17 |  |  |
|  | Em que zona? |  | front-page.php:54 |  |  |
|  | Localidade |  | front-page.php:85, front-page.php:89 |  |  |
|  | Em que vila? |  | front-page.php:86 |  |  |
|  | Ver a lista |  | front-page.php:129, inc/map-data.php:173 |  |  |
|  | Navegaçom principal |  | header.php:35 | before: Main navigation |  |
|  | O tema silveira precisa do plugin Advanced Custom Fields (ACF). Sem ele, os campos dos projetos (lema, endereço e coordenadas) nom estám disponíveis e o mapa da portada aparece sem pontos. |  | inc/acf.php:54 |  |  |
|  | Eventos | Post Type General Name | inc/cpt-eventos.php:13 |  |  |
|  | Evento | Post Type Singular Name | inc/cpt-eventos.php:14 |  |  |
|  | Agenda |  | inc/cpt-eventos.php:15 |  |  |
|  | Todos os Eventos |  | inc/cpt-eventos.php:16 |  |  |
| ★ | Adicionar Novo Evento |  | inc/cpt-eventos.php:17 |  | "Adicionar"/"Buscar": see general doubt |
| ★ | Adicionar Novo |  | inc/cpt-eventos.php:18, inc/cpt-projetos.php:43, inc/cpt-recursos.php:51 |  | "Adicionar"/"Buscar": see general doubt |
|  | Novo Evento |  | inc/cpt-eventos.php:19 |  |  |
|  | Editar Evento |  | inc/cpt-eventos.php:20 |  |  |
|  | Atualizar Evento |  | inc/cpt-eventos.php:21 |  |  |
|  | Ver Evento |  | inc/cpt-eventos.php:22 |  |  |
| ★ | Buscar Evento |  | inc/cpt-eventos.php:23 |  | "Adicionar"/"Buscar": see general doubt |
|  | Nom encontrado |  | inc/cpt-eventos.php:24, inc/cpt-projetos.php:49, inc/cpt-recursos.php:57 | before: Não encontrado |  |
| ★ | Nom encontrado no lixo |  | inc/cpt-eventos.php:25, inc/cpt-projetos.php:50, inc/cpt-recursos.php:58 | before: Não encontrado na lixeira | "lixo" / "papeleira" / "lixeira" for the WordPress trash |
|  | Evento |  | inc/cpt-eventos.php:28 |  |  |
|  | Modalidades | Taxonomy General Name | inc/cpt-projetos.php:14 |  |  |
|  | Modalidade | Taxonomy Singular Name | inc/cpt-projetos.php:15 |  |  |
|  | Modalidade |  | inc/cpt-projetos.php:16 |  |  |
|  | Todas as Modalidades |  | inc/cpt-projetos.php:17 |  |  |
|  | Nova Modalidade |  | inc/cpt-projetos.php:18 |  |  |
| ★ | Adicionar Nova Modalidade |  | inc/cpt-projetos.php:19 |  | "Adicionar"/"Buscar": see general doubt |
|  | Editar Modalidade |  | inc/cpt-projetos.php:20 |  |  |
|  | Atualizar Modalidade |  | inc/cpt-projetos.php:21 |  |  |
|  | Ver Modalidade |  | inc/cpt-projetos.php:22 |  |  |
|  | Projetos | Post Type General Name | inc/cpt-projetos.php:38 |  |  |
|  | Projeto | Post Type Singular Name | inc/cpt-projetos.php:39 |  |  |
|  | Projetos |  | inc/cpt-projetos.php:40 |  |  |
|  | Todos os Projetos |  | inc/cpt-projetos.php:41 |  |  |
| ★ | Adicionar Novo Projeto |  | inc/cpt-projetos.php:42 |  | "Adicionar"/"Buscar": see general doubt |
|  | Novo Projeto |  | inc/cpt-projetos.php:44 |  |  |
|  | Editar Projeto |  | inc/cpt-projetos.php:45 |  |  |
|  | Atualizar Projeto |  | inc/cpt-projetos.php:46 |  |  |
|  | Ver Projeto |  | inc/cpt-projetos.php:47 |  |  |
| ★ | Buscar Projeto |  | inc/cpt-projetos.php:48 |  | "Adicionar"/"Buscar": see general doubt |
|  | Projeto |  | inc/cpt-projetos.php:53 |  |  |
|  | Categorias de Recursos | Taxonomy General Name | inc/cpt-recursos.php:14 |  |  |
|  | Categoria de Recurso | Taxonomy Singular Name | inc/cpt-recursos.php:15 |  |  |
|  | Categorias |  | inc/cpt-recursos.php:16 |  |  |
|  | Todas as Categorias |  | inc/cpt-recursos.php:17 |  |  |
| ★ | Categoria superior |  | inc/cpt-recursos.php:18 | before: Categoria Pai | "superior" / "mai" for the parent category |
| ★ | Categoria superior: |  | inc/cpt-recursos.php:19 | before: Categoria Pai: | same |
|  | Nova Categoria |  | inc/cpt-recursos.php:20 |  |  |
| ★ | Adicionar Nova Categoria |  | inc/cpt-recursos.php:21 |  | "Adicionar"/"Buscar": see general doubt |
|  | Editar Categoria |  | inc/cpt-recursos.php:22 |  |  |
|  | Atualizar Categoria |  | inc/cpt-recursos.php:23 |  |  |
|  | Ver Categoria |  | inc/cpt-recursos.php:24 |  |  |
|  | Geral |  | inc/cpt-recursos.php:38 |  |  |
|  | Recursos | Post Type General Name | inc/cpt-recursos.php:46 |  |  |
|  | Recurso | Post Type Singular Name | inc/cpt-recursos.php:47 |  |  |
|  | Recursos |  | inc/cpt-recursos.php:48 |  |  |
|  | Todos os Recursos |  | inc/cpt-recursos.php:49 |  |  |
| ★ | Adicionar Novo Recurso |  | inc/cpt-recursos.php:50 |  | "Adicionar"/"Buscar": see general doubt |
|  | Novo Recurso |  | inc/cpt-recursos.php:52 |  |  |
|  | Editar Recurso |  | inc/cpt-recursos.php:53 |  |  |
|  | Atualizar Recurso |  | inc/cpt-recursos.php:54 |  |  |
|  | Ver Recurso |  | inc/cpt-recursos.php:55 |  |  |
| ★ | Buscar Recurso |  | inc/cpt-recursos.php:56 |  | "Adicionar"/"Buscar": see general doubt |
|  | Recurso |  | inc/cpt-recursos.php:61 |  |  |
|  | O plugin ACF nom está ativo. |  | inc/geocoding-cli.php:37 |  |  |
|  | Nom há projetos com endereço e sem coordenadas. |  | inc/geocoding-cli.php:64 |  |  |
| ★ | %d projetos por geocodificar (--dry-run: sem fazer petiçons). |  | inc/geocoding-cli.php:81 |  | "petiçons" |
|  | %1$d geocodificados, %2$d com falhas |  | inc/geocoding-cli.php:119 |  |  |
|  | Nom se encontrárom coordenadas na Galiza para o endereço deste projeto, polo que nom aparecerá no mapa ou ficará no ponto anterior. Revê o endereço ou introduz a latitude e a longitude manualmente. |  | inc/geocoding.php:236 |  |  |
| ★ | Nom foi possível contactar com o serviço de geolocalizaçom e as coordenadas do projeto nom mudárom. Volta guardar o projeto mais tarde ou introduz a latitude e a longitude manualmente. |  | inc/geocoding.php:238 |  | "Volta guardar" (periphrasis) / "Volta a guardar" |
|  | Ver projeto |  | inc/map-data.php:172 | before: (literal in map.js) |  |
| ★ | Acochar a lista |  | inc/map-data.php:174 |  | "Acochar" / "Ocultar" |
|  | Nom há projetos para estes filtros. |  | inc/map-data.php:175 |  |  |
| ★ | %d selecionada / %d selecionadas |  | inc/map-data.php:177, inc/map-data.php:179 | new | "selecionada" / "seleccionada" / "marcada" |
|  | Menu principal |  | inc/setup.php:25 | before: Main Menu |  |
|  | Territórios | Taxonomy General Name | inc/tax-shared.php:13 |  |  |
|  | Território | Taxonomy Singular Name | inc/tax-shared.php:14 |  |  |
|  | Território |  | inc/tax-shared.php:15, template-parts/content-projeto.php:56 |  |  |
|  | Todos os Territórios |  | inc/tax-shared.php:16 |  |  |
|  | Comarca: |  | inc/tax-shared.php:18 |  |  |
|  | Novo Território |  | inc/tax-shared.php:19 |  |  |
| ★ | Adicionar Novo Território |  | inc/tax-shared.php:20 |  | "Adicionar"/"Buscar": see general doubt |
|  | Editar Território |  | inc/tax-shared.php:21 |  |  |
|  | Atualizar Território |  | inc/tax-shared.php:22 |  |  |
|  | Ver Território |  | inc/tax-shared.php:23 |  |  |
| ★ | Buscar Territórios |  | inc/tax-shared.php:24 |  | "Adicionar"/"Buscar": see general doubt |
|  | Sem territórios |  | inc/tax-shared.php:26 |  |  |
| ★ | Um ecossistema para o galego |  | inc/template-tags.php:17 | before: Um ecosistema para o galego (without gettext) | spelling "ecossistema" (previously "ecosistema", text from Figma) |
|  | Resultados para: %s |  | inc/template-tags.php:34 |  |  |
|  | Página nom encontrada |  | inc/template-tags.php:82 |  |  |
|  | Anterior |  | inc/template-tags.php:102 | new |  |
| ★ | Seguinte |  | inc/template-tags.php:103 | new | "Seguinte" / "Próxima" |
| ★ | Navegaçom entre páginas |  | inc/template-tags.php:104 | new | wording |
|  | Páginas |  | inc/template-tags.php:105 | new |  |
| ★ | Procurar por: |  | searchform.php:12 | new | same |
| ★ | Procurar… |  | searchform.php:14 | new | "Procurar" / "Buscar" (the admin labels use "Buscar") |
| ★ | Procurar |  | searchform.php:15 | new | same |
|  | Nada encontrado |  | template-parts/content-none.php:12 |  |  |
| ★ | Queres publicar a tua primeira entrada? <a href="%1$s">Começa aqui</a>. |  | template-parts/content-none.php:22 | before: Pronto para publicar o seu primeiro post? <a href="%1$s">Comece aqui</a>. | addressing the reader as "tu"; "entrada" for post |
| ★ | Desculpa, mas nada corresponde à tua procura. Tenta de novo com outras palavras. |  | template-parts/content-none.php:34 | before: Desculpe, mas nada corresponde aos seus termos de busca. Por favor, tente novamente con palavras-chave diferentes. | addressing the reader as "tu" |
| ★ | Parece que nom conseguimos encontrar o que procuras. Talvez umha procura te poda ajudar. |  | template-parts/content-none.php:37 | before: Parece que não conseguimos encontrar o que você está procurando. Talvez a busca pueda ayudar. | "poda" / "possa" |
|  | Endereço |  | template-parts/content-projeto.php:46 |  |  |
