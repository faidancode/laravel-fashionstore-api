# Variabel untuk kemudahan penyesuaian
PHP = php
ARTISAN = $(PHP) artisan
COMPOSER = composer
NPM = npm

# Perintah Default (saat hanya mengetik 'make')
help:
	@echo "Laravel Management Commands:"
	@echo "  make start      - Menjalankan server, queue, dan vite secara bersamaan"
	@echo "  make install    - Install semua dependency (PHP & JS)"
	@echo "  make build      - Build aset untuk produksi"
	@echo "  make migrate    - Jalankan database migration"
	@echo "  make fresh      - Reset database dan jalankan seeder"
	@echo "  make test       - Jalankan semua unit & feature tests"
	@echo "  make clean      - Hapus semua cache (config, route, view)"
	@echo "  make tinker     - Masuk ke shell Laravel Tinker"

# --- Development ---

start:
	$(COMPOSER) run dev

install:
	$(COMPOSER) install
	$(NPM) install

build:
	$(NPM) run build

# --- Database ---

migrate:
	$(ARTISAN) migrate

fresh:
	$(ARTISAN) migrate:fresh --seed

# --- Testing ---

test:
	$(PHP) vendor/bin/phpunit

test-filter:
	$(PHP) vendor/bin/phpunit --filter $(filter)

# --- Maintenance ---

clean:
	$(ARTISAN) config:clear
	$(ARTISAN) route:clear
	$(ARTISAN) view:clear
	$(ARTISAN) cache:clear
	@echo "Semua cache telah dibersihkan!"

tinker:
	$(ARTISAN) tinker

	# --- Generators ---

# Penggunaan: make migration name=create_products_table
migration:
	$(ARTISAN) make:migration $(name)

# Penggunaan: make model name=Product
model:
	$(ARTISAN) make:model $(name)

# Penggunaan: make controller name=ProductController
controller:
	$(ARTISAN) make:controller $(name)

# Penggunaan: make resource name=Product (Membuat Model, Migration, & Controller sekaligus)
resource:
	$(ARTISAN) make:model $(name) -mc

	# =========================
# DOCKER
# =========================
.PHONY: docker-up
docker-up:
	docker-compose up -d --build

.PHONY: docker-down
docker-down:
	docker-compose down

.PHONY: docker-infra
docker-infra:
	docker-compose up -d postgres

.PHONY: docker-infra-stop
docker-infra-stop:
	docker-compose stop postgres