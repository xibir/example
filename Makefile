PROJECT_NAME = example

FPM_CONTAINER = $(PROJECT_NAME)_fpm
MYSQL_CONTAINER = $(PROJECT_NAME)_mysql
MYSQL_ROOT_PASS = dbpass
HELM_DIR=deploy/helm

up:
	minikube start

down:
	minikube stop

build:
	docker build -t xibir-app-fpm:latest -f deploy/fpm/Dockerfile . && \
	docker build -t xibir-app-nginx:latest -f deploy/nginx/Dockerfile . && \
	minikube image load xibir-app-fpm:latest && \
	minikube image load xibir-app-nginx:latest

helm-update:
	helm upgrade --install xibir-app $(HELM_DIR) \
 	--set backend.fpm.image.name=docker.io/library/xibir-app-fpm \
	--set backend.fpm.image.tag=latest \
	--set backend.nginx.image.name=docker.io/library/xibir-app-nginx \
	--set backend.nginx.image.tag=latest

docker-up:
	docker compose up -d

docker-stop:
	docker compose down
