# Desafio Web Valemobi

Este projeto foi realizado como o desafio Web proposto para o processo seletivo da Valemobi.

O sistema está hospedado no Heroku. Clique [aqui](https://desolate-ravine-37603.herokuapp.com/) para acessá-lo.

# URLs importantes
* /db/setup/   - Realiza a criacao das tabelas e sequencias necessárias
* /db/reset/   - Reinicializa o banco, apagando todos os dados.

# RESTFUL-ish API

* /api/mercadorias/
* /api/mercadorias/{id}
* /api/operacoes/
* /api/operacoes/{id}	

Foram implementados apenas os métodos GET e POST, pois eles foram suficientes para realização do desafio. Nas URLS de coleções, o método GET recupera todos os itens e o método POST adiciona um item novo, gerando erro se o item ja existe. Nas URLs de itens, o método GET recupera o item. O método POST não foi implementado para itens.


