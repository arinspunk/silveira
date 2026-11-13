# Inventário de textos do theme silveira — galego reintegrado ("umha"/"-çom"/"nom")

Para regenerá-lo: `wp i18n make-pot wp-content/themes/silveira /tmp/silveira.pot --domain=silveira --exclude=vendor,node_modules,assets,public,template-kitchen-sink.php`.

Gerado com `wp i18n make-pot` (domínio `silveira`, sem vendor/node_modules/assets nem a Kitchen Sink) ao aplicar o change `i18n-reintegrado`.

**Para a revisom lingüística (posterior e nom bloqueante):** os textos marcados com ★ som dúvidas concretas; o resto pode rever-se igualmente. As correçons aplicam-se num change de seguimento.

**Dúvida geral ★:** as etiquetas do admin estám em maiúsculas de título ("Adicionar Novo Projeto", "Todos os Eventos"); a norma galega usaria só a inicial ("Adicionar novo projeto"). Também "Adicionar" / "Engadir" / "Acrescentar" e "Buscar" / "Procurar".

Total: 111 textos.

| ★ | Texto | Contexto | Ficheiro | Mudança neste change | Nota |
|---|---|---|---|---|---|
|  | Silveira |  | style.css |  |  |
|  | http://silveira.localhost |  | style.css |  |  |
|  | Tema de desenvolvimento para o projeto silveira. |  | style.css | antes: Theme de desarrollo para el proyecto silveira. |  |
| ★ | Mostrar o rodapé |  | footer.php:13 |  | "rodapé" / "pé de página" |
|  | Umha sebe coidada com agarimo pola %s |  | footer.php:28 |  |  |
|  | Semente |  | footer.php:29 |  |  |
|  | Navegaçom do rodapé |  | footer.php:46 | antes: Footer navigation |  |
|  | Modalidade educativa |  | front-page.php:22, front-page.php:26 |  |  |
|  | Que procuras? |  | front-page.php:23 |  |  |
|  | Comarca |  | front-page.php:53, front-page.php:57, inc/tax-shared.php:17 |  |  |
|  | Em que zona? |  | front-page.php:54 |  |  |
|  | Localidade |  | front-page.php:85, front-page.php:89 |  |  |
|  | Em que vila? |  | front-page.php:86 |  |  |
|  | Ver a lista |  | front-page.php:129, inc/map-data.php:173 |  |  |
|  | Navegaçom principal |  | header.php:35 | antes: Main navigation |  |
|  | O tema silveira precisa do plugin Advanced Custom Fields (ACF). Sem ele, os campos dos projetos (lema, endereço e coordenadas) nom estám disponíveis e o mapa da portada aparece sem pontos. |  | inc/acf.php:54 |  |  |
|  | Eventos | Post Type General Name | inc/cpt-eventos.php:13 |  |  |
|  | Evento | Post Type Singular Name | inc/cpt-eventos.php:14 |  |  |
|  | Agenda |  | inc/cpt-eventos.php:15 |  |  |
|  | Todos os Eventos |  | inc/cpt-eventos.php:16 |  |  |
| ★ | Adicionar Novo Evento |  | inc/cpt-eventos.php:17 |  | "Adicionar"/"Buscar": ver dúvida geral |
| ★ | Adicionar Novo |  | inc/cpt-eventos.php:18, inc/cpt-projetos.php:43, inc/cpt-recursos.php:51 |  | "Adicionar"/"Buscar": ver dúvida geral |
|  | Novo Evento |  | inc/cpt-eventos.php:19 |  |  |
|  | Editar Evento |  | inc/cpt-eventos.php:20 |  |  |
|  | Atualizar Evento |  | inc/cpt-eventos.php:21 |  |  |
|  | Ver Evento |  | inc/cpt-eventos.php:22 |  |  |
| ★ | Buscar Evento |  | inc/cpt-eventos.php:23 |  | "Adicionar"/"Buscar": ver dúvida geral |
|  | Nom encontrado |  | inc/cpt-eventos.php:24, inc/cpt-projetos.php:49, inc/cpt-recursos.php:57 | antes: Não encontrado |  |
| ★ | Nom encontrado no lixo |  | inc/cpt-eventos.php:25, inc/cpt-projetos.php:50, inc/cpt-recursos.php:58 | antes: Não encontrado na lixeira | "lixo" / "papeleira" / "lixeira" para a papeleira do WordPress |
|  | Evento |  | inc/cpt-eventos.php:28 |  |  |
|  | Modalidades | Taxonomy General Name | inc/cpt-projetos.php:14 |  |  |
|  | Modalidade | Taxonomy Singular Name | inc/cpt-projetos.php:15 |  |  |
|  | Modalidade |  | inc/cpt-projetos.php:16 |  |  |
|  | Todas as Modalidades |  | inc/cpt-projetos.php:17 |  |  |
|  | Nova Modalidade |  | inc/cpt-projetos.php:18 |  |  |
| ★ | Adicionar Nova Modalidade |  | inc/cpt-projetos.php:19 |  | "Adicionar"/"Buscar": ver dúvida geral |
|  | Editar Modalidade |  | inc/cpt-projetos.php:20 |  |  |
|  | Atualizar Modalidade |  | inc/cpt-projetos.php:21 |  |  |
|  | Ver Modalidade |  | inc/cpt-projetos.php:22 |  |  |
|  | Projetos | Post Type General Name | inc/cpt-projetos.php:38 |  |  |
|  | Projeto | Post Type Singular Name | inc/cpt-projetos.php:39 |  |  |
|  | Projetos |  | inc/cpt-projetos.php:40 |  |  |
|  | Todos os Projetos |  | inc/cpt-projetos.php:41 |  |  |
| ★ | Adicionar Novo Projeto |  | inc/cpt-projetos.php:42 |  | "Adicionar"/"Buscar": ver dúvida geral |
|  | Novo Projeto |  | inc/cpt-projetos.php:44 |  |  |
|  | Editar Projeto |  | inc/cpt-projetos.php:45 |  |  |
|  | Atualizar Projeto |  | inc/cpt-projetos.php:46 |  |  |
|  | Ver Projeto |  | inc/cpt-projetos.php:47 |  |  |
| ★ | Buscar Projeto |  | inc/cpt-projetos.php:48 |  | "Adicionar"/"Buscar": ver dúvida geral |
|  | Projeto |  | inc/cpt-projetos.php:53 |  |  |
|  | Categorias de Recursos | Taxonomy General Name | inc/cpt-recursos.php:14 |  |  |
|  | Categoria de Recurso | Taxonomy Singular Name | inc/cpt-recursos.php:15 |  |  |
|  | Categorias |  | inc/cpt-recursos.php:16 |  |  |
|  | Todas as Categorias |  | inc/cpt-recursos.php:17 |  |  |
| ★ | Categoria superior |  | inc/cpt-recursos.php:18 | antes: Categoria Pai | "superior" / "mai" para a categoria pai |
| ★ | Categoria superior: |  | inc/cpt-recursos.php:19 | antes: Categoria Pai: | idem |
|  | Nova Categoria |  | inc/cpt-recursos.php:20 |  |  |
| ★ | Adicionar Nova Categoria |  | inc/cpt-recursos.php:21 |  | "Adicionar"/"Buscar": ver dúvida geral |
|  | Editar Categoria |  | inc/cpt-recursos.php:22 |  |  |
|  | Atualizar Categoria |  | inc/cpt-recursos.php:23 |  |  |
|  | Ver Categoria |  | inc/cpt-recursos.php:24 |  |  |
|  | Geral |  | inc/cpt-recursos.php:38 |  |  |
|  | Recursos | Post Type General Name | inc/cpt-recursos.php:46 |  |  |
|  | Recurso | Post Type Singular Name | inc/cpt-recursos.php:47 |  |  |
|  | Recursos |  | inc/cpt-recursos.php:48 |  |  |
|  | Todos os Recursos |  | inc/cpt-recursos.php:49 |  |  |
| ★ | Adicionar Novo Recurso |  | inc/cpt-recursos.php:50 |  | "Adicionar"/"Buscar": ver dúvida geral |
|  | Novo Recurso |  | inc/cpt-recursos.php:52 |  |  |
|  | Editar Recurso |  | inc/cpt-recursos.php:53 |  |  |
|  | Atualizar Recurso |  | inc/cpt-recursos.php:54 |  |  |
|  | Ver Recurso |  | inc/cpt-recursos.php:55 |  |  |
| ★ | Buscar Recurso |  | inc/cpt-recursos.php:56 |  | "Adicionar"/"Buscar": ver dúvida geral |
|  | Recurso |  | inc/cpt-recursos.php:61 |  |  |
|  | O plugin ACF nom está ativo. |  | inc/geocoding-cli.php:37 |  |  |
|  | Nom há projetos com endereço e sem coordenadas. |  | inc/geocoding-cli.php:64 |  |  |
| ★ | %d projetos por geocodificar (--dry-run: sem fazer petiçons). |  | inc/geocoding-cli.php:81 |  | "petiçons" |
|  | %1$d geocodificados, %2$d com falhas |  | inc/geocoding-cli.php:119 |  |  |
|  | Nom se encontrárom coordenadas na Galiza para o endereço deste projeto, polo que nom aparecerá no mapa ou ficará no ponto anterior. Revê o endereço ou introduz a latitude e a longitude manualmente. |  | inc/geocoding.php:236 |  |  |
| ★ | Nom foi possível contactar com o serviço de geolocalizaçom e as coordenadas do projeto nom mudárom. Volta guardar o projeto mais tarde ou introduz a latitude e a longitude manualmente. |  | inc/geocoding.php:238 |  | "Volta guardar" (perífrase) / "Volta a guardar" |
|  | Ver projeto |  | inc/map-data.php:172 | antes: (literal em map.js) |  |
| ★ | Acochar a lista |  | inc/map-data.php:174 |  | "Acochar" / "Ocultar" |
|  | Nom há projetos para estes filtros. |  | inc/map-data.php:175 |  |  |
| ★ | %d selecionada / %d selecionadas |  | inc/map-data.php:177, inc/map-data.php:179 | novo | "selecionada" / "seleccionada" / "marcada" |
|  | Menu principal |  | inc/setup.php:25 | antes: Main Menu |  |
|  | Territórios | Taxonomy General Name | inc/tax-shared.php:13 |  |  |
|  | Território | Taxonomy Singular Name | inc/tax-shared.php:14 |  |  |
|  | Território |  | inc/tax-shared.php:15, template-parts/content-projeto.php:56 |  |  |
|  | Todos os Territórios |  | inc/tax-shared.php:16 |  |  |
|  | Comarca: |  | inc/tax-shared.php:18 |  |  |
|  | Novo Território |  | inc/tax-shared.php:19 |  |  |
| ★ | Adicionar Novo Território |  | inc/tax-shared.php:20 |  | "Adicionar"/"Buscar": ver dúvida geral |
|  | Editar Território |  | inc/tax-shared.php:21 |  |  |
|  | Atualizar Território |  | inc/tax-shared.php:22 |  |  |
|  | Ver Território |  | inc/tax-shared.php:23 |  |  |
| ★ | Buscar Territórios |  | inc/tax-shared.php:24 |  | "Adicionar"/"Buscar": ver dúvida geral |
|  | Sem territórios |  | inc/tax-shared.php:26 |  |  |
| ★ | Um ecossistema para o galego |  | inc/template-tags.php:17 | antes: Um ecosistema para o galego (sem gettext) | grafia "ecossistema" (antes "ecosistema", texto do Figma) |
|  | Resultados para: %s |  | inc/template-tags.php:34 |  |  |
|  | Página nom encontrada |  | inc/template-tags.php:82 |  |  |
|  | Anterior |  | inc/template-tags.php:102 | novo |  |
| ★ | Seguinte |  | inc/template-tags.php:103 | novo | "Seguinte" / "Próxima" |
| ★ | Navegaçom entre páginas |  | inc/template-tags.php:104 | novo | formulaçom |
|  | Páginas |  | inc/template-tags.php:105 | novo |  |
| ★ | Procurar por: |  | searchform.php:12 | novo | idem |
| ★ | Procurar… |  | searchform.php:14 | novo | "Procurar" / "Buscar" (as etiquetas do admin usam "Buscar") |
| ★ | Procurar |  | searchform.php:15 | novo | idem |
|  | Nada encontrado |  | template-parts/content-none.php:12 |  |  |
| ★ | Queres publicar a tua primeira entrada? <a href="%1$s">Começa aqui</a>. |  | template-parts/content-none.php:22 | antes: Pronto para publicar o seu primeiro post? <a href="%1$s">Comece aqui</a>. | tratamento por "tu"; "entrada" para post |
| ★ | Desculpa, mas nada corresponde à tua procura. Tenta de novo com outras palavras. |  | template-parts/content-none.php:34 | antes: Desculpe, mas nada corresponde aos seus termos de busca. Por favor, tente novamente con palavras-chave diferentes. | tratamento por "tu" |
| ★ | Parece que nom conseguimos encontrar o que procuras. Talvez umha procura te poda ajudar. |  | template-parts/content-none.php:37 | antes: Parece que não conseguimos encontrar o que você está procurando. Talvez a busca pueda ayudar. | "poda" / "possa" |
|  | Endereço |  | template-parts/content-projeto.php:46 |  |  |
