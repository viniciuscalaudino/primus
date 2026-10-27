
# Etapa 1: construir a aplicação
FROM node:22-alpine AS build

# Define a pasta de trabalho dentro do container
WORKDIR /app

# Copia os arquivos de dependências
COPY package*.json ./

# Instala as dependências do projeto
RUN npm install

# Copia o restante dos arquivos do front-end
COPY . .

# Gera a versão de produção da aplicação
RUN npm run build


# Etapa 2: disponibilizar a aplicação com Nginx
FROM nginx:alpine

# Copia os arquivos gerados pelo build para o Nginx
COPY --from=build /app/dist /usr/share/nginx/html

# Informa que a aplicação utiliza a porta 80
EXPOSE 80

# Mantém o Nginx em execução
CMD ["nginx", "-g", "daemon off;"]