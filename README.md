# PRIMUS

Loja virtual de suplementos e acessórios. Projeto final: API Laravel 12 (MVC + Service Layer),
frontend em HTML/CSS/JS (Vite), MySQL, Docker e Swagger UI.

## Como rodar

Pré-requisito: Docker com Compose. Na raiz do projeto:

```bash
cp api/.env.example api/.env
docker compose up --build -d
docker compose exec api php artisan key:generate
docker compose exec api php artisan migrate --seed
```

Para recriar o banco do zero: `docker compose exec api php artisan migrate:fresh --seed`.

| O quê | Endereço |
|---|---|
| Loja (frontend) | http://localhost |
| API | http://localhost/api |
| Swagger UI | http://localhost/docs/ |
| Imagens enviadas | http://localhost/storage/products/... |

Tudo passa pelo proxy (porta 80), então front e API ficam na **mesma origem**: não há CORS e o cookie de
sessão funciona. A porta 80 precisa estar livre (pare o Apache/nginx local, se houver).

A URL da API no frontend vem da variável `VITE_API_URL` (padrão `/api`, mesma origem).
Para outra URL: `VITE_API_URL=https://minha-api.com/api docker compose up --build`.

## Credenciais dos seeders

Senha de todos: `senha123`

| Usuário | E-mail | Papel |
|---|---|---|
| Admin | admin@primus.com | admin |
| Cliente 1 | cliente1@primus.com | customer (1 pedido aguardando pagamento e 1 pago) |
| Cliente 2 | cliente2@primus.com | customer (pedidos enviado, entregue e cancelado) |
| Cliente 3 | cliente3@primus.com | customer (carrinho com itens, sem pedidos) |

Também são criados 4 categorias, 32 produtos (um sem estoque: *Hipercalórico 3kg*; um inativo:
*Arginina*) e um carrinho de visitante com o token `11111111-1111-4111-8111-111111111111`.

## Autenticação

Sessão por cookie (Laravel Sanctum, modo SPA):

1. `GET /sanctum/csrf-cookie`
2. Em `POST`/`PUT`/`PATCH`/`DELETE`, enviar o cookie `XSRF-TOKEN` (decodificado) no header `X-XSRF-TOKEN`.
3. `POST /api/auth/login` cria a sessão; `POST /api/auth/logout` a encerra.

Login e registro têm limite de 6 tentativas por minuto (429). Rotas `/api/admin/*` exigem `role = admin` (403 caso contrário).

## Regras de negócio

**Fusão do carrinho.** O visitante tem um carrinho identificado por um `guest_token` (UUID gerado pelo
servidor), enviado no header `X-Cart-Token`. No login ou registro, se o header vier:

- itens do visitante são somados aos da conta; a quantidade de um mesmo produto é **somada e limitada ao estoque**;
- produto inativo ou sem estoque não é levado; produto com quantidade ajustada gera aviso;
- os avisos voltam em `cart_warnings.products[]` na resposta;
- o carrinho de visitante é apagado após a fusão.

**Pedidos.** Criados em transação: valida estoque (produtos travados em ordem de id), congela nome e preço dos
itens e o endereço, baixa o estoque e esvazia o carrinho.

- Frete: R$ 19,90; **grátis quando o subtotal for maior ou igual a R$ 299,00**.
- Status: `awaiting_payment → paid → shipped → delivered`; `awaiting_payment → cancelled`. Transição inválida retorna 409.
- Cancelar devolve o estoque. Pagar/cancelar pedido de outro usuário retorna 403.

**Admin.** `DELETE /api/admin/products/{id}` **desativa** o produto (não apaga). Categoria com produtos não pode ser excluída (409).

**Imagens.** O produto é criado/atualizado com `multipart/form-data` e o campo `image` (jpg/png/webp até 2 MB).
O arquivo vai para o volume `product_images` e é servido em `/storage/...`.

**Perfil.** `PATCH /api/auth/me` altera nome e e-mail do usuário logado.

## Estrutura

```
api/        Laravel (Controllers → Services → Models, Resources, FormRequests)
frontend/   HTML/CSS/JS com Vite
proxy/      nginx: /api, /sanctum, /storage → api; /docs → swagger; / → frontend
docs/       openapi.yaml (lido pelo Swagger UI)
compose.yaml
```
