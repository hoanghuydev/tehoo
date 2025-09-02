## Dev Container: Kiến trúc, mounts và thứ tự thực thi

Tài liệu này giải thích chi tiết cách bộ devcontainer vận hành dựa trên `/.devcontainer/devcontainer.json` và `/.devcontainer/docker-compose.yml`, gồm: thành phần dịch vụ, cách mount, cổng, biến môi trường, và trình tự chạy khi bạn mở dự án trong Dev Containers.

### 1) Thành phần chính

- **Devcontainer entry (service chính)**: `laravel-app` (PHP-FPM, Laravel)
- **Các dịch vụ được khởi chạy**: `laravel-app`, `nextjs-app`, `db` (MySQL), `redis` (Redis Stack), `nginx`
- **Network**: `app-network` (bridge)
- **Volumes đặt tên**: `mysql-data`, `redis-data`
- **Mount bind dự án**: `..:/workspace` (gắn toàn bộ repo vào `/workspace` trong container)
- **Mount ẩn thư mục phụ thuộc**: `/workspace/backend/vendor`, `/workspace/frontend/node_modules` (volume ẩn để không ghi đè node_modules/vendor của host)

### 2) Giải thích `/.devcontainer/devcontainer.json`

- `name: "Tehoo"`: Tên devcontainer hiển thị trong môi trường phát triển.
- `dockerComposeFile: "docker-compose.yml"`: Sử dụng docker-compose ở thư mục `/.devcontainer`.
- `service: "laravel-app"`: Container chính bạn attach terminal/VS Code vào.
- `workspaceFolder: "/workspace"`: Thư mục làm việc bên trong container chính.
- `shutdownAction: "stopCompose"`: Khi đóng workspace, dừng toàn bộ stack compose.
- `runServices`: Danh sách dịch vụ sẽ được `up`: `laravel-app`, `nextjs-app`, `db`, `redis`, `nginx`.
- `customizations.vscode.extensions`: Danh sách extensions cài trong môi trường dev.
- `forwardPorts: [8080, 9000, 3000, 3306, 6379]`:
  - 8080: Nginx (proxy Laravel)
  - 9000: PHP-FPM/Xdebug trong `laravel-app`
  - 3000: Next.js dev server
  - 3306: MySQL
  - 6379: Redis
- `postCreateCommand`: Chạy sau khi container sẵn sàng và mount xong:
  - `composer install --working-dir=/workspace/backend`
  - `php artisan key:generate --working-dir=/workspace/backend`
- `remoteUser: "root"`: Người dùng trong container khi attach.

### 3) Giải thích `/.devcontainer/docker-compose.yml` theo khối

#### 3.1) `laravel-app` (PHP-FPM)

- `build.context: ./docker/backend`, `dockerfile: Dockerfile`: Build image Laravel từ `/.devcontainer/docker/backend/Dockerfile`.
- `container_name: laravel-app`
- `working_dir: /workspace/backend`: Thư mục làm việc mặc định.
- `volumes`:
  - `..:/workspace`: Bind toàn bộ repo host vào container.
  - `/workspace/backend/vendor`: Volume ẩn cho dependencies PHP (tránh ghi đè của host).
  - `/workspace/frontend/node_modules`: Volume ẩn cho dependencies frontend (cần nếu Laravel gọi đến assets, hoặc chia sẻ cho tiện).
- `networks: [app-network]`
- `depends_on: [db, redis]`: Chỉ đảm bảo thứ tự khởi động, không đợi DB/Redis “ready”.
- `env_file: ../backend/.env`: Nạp biến môi trường Laravel từ host.
- `environment: XDEBUG_CONFIG=client_host=host.docker.internal`: Cấu hình Xdebug trỏ về host.

Kết quả: Container Laravel chạy PHP-FPM, thấy mã nguồn ở `/workspace/backend`, cài đặt vendor vào volume ẩn. Có thể kết nối `db`, `redis` qua tên dịch vụ trong network.

#### 3.2) `nextjs-app`

- `build.context: ../frontend`, `dockerfile: ../.devcontainer/docker/frontend/Dockerfile`: Build image Next.js từ Dockerfile custom.
- `container_name: nextjs-app`
- `working_dir: /workspace/frontend`
- `volumes`:
  - `..:/workspace`: Chia sẻ code host.
  - `/workspace/frontend/node_modules`: Volume ẩn cho dependencies Node.
- `ports: "3000:3000"`: Mở Next.js ra host tại `http://localhost:3000`.
- `networks: [app-network]`
- `env_file: ../frontend/.env.local`, `environment: NODE_ENV=development`.

Kết quả: Next.js dev server chạy và hot-reload theo code trong host.

#### 3.3) `nginx`

- `image: nginx:alpine`: Dùng image chính thức.
- `ports: "8080:80"`: Xuất Nginx ra host `http://localhost:8080`.
- `volumes`:
  - `..:/workspace`: Nginx có thể serve static nếu cấu hình.
  - `./docker/backend/nginx.conf:/etc/nginx/conf.d/default.conf`: Ánh xạ cấu hình Nginx tùy biến từ `/.devcontainer/docker/backend/nginx.conf`.
- `networks: [app-network]`
- `depends_on: [laravel-app]`

Kết quả: Nginx reverse proxy đến PHP-FPM trong `laravel-app` (tùy config), phục vụ Laravel qua cổng 8080.

#### 3.4) `db` (MySQL 8.0)

- `image: mysql:8.0`
- `ports: "3306:3306"`: Truy cập MySQL từ host qua `localhost:3306`.
- `environment` mặc định hóa từ `.env` nếu có: `MYSQL_DATABASE`, `MYSQL_USER`, `MYSQL_PASSWORD`, `MYSQL_ROOT_PASSWORD`.
- `volumes: mysql-data:/var/lib/mysql`: Dữ liệu bền vững qua volume đặt tên.
- `networks: [app-network]`

Kết quả: DB chạy độc lập, dữ liệu nằm trong volume `mysql-data`.

#### 3.5) `redis` (redis/redis-stack)

- `image: redis/redis-stack:latest`
- `ports: "6379:6379"`: Truy cập Redis từ host qua `localhost:6379`.
- `volumes: redis-data:/data`: Lưu dữ liệu Redis (nếu bật persistence).
- `networks: [app-network]`

Kết quả: Redis phục vụ cache/queue; app truy cập bằng hostname `redis` trong network.

#### 3.6) `networks` và `volumes`

- `networks.app-network: driver: bridge`: Mặc định bridge network để các container thấy nhau bằng tên dịch vụ.
- `volumes.mysql-data`, `volumes.redis-data`: Volumes đặt tên để dữ liệu bền vững.

### 4) Cách mount và tác dụng

- **Bind mount repo**: `..:/workspace`
  - Mọi thay đổi code trên host phản ánh ngay trong container (hot-reload cho Next.js, reload PHP).
- **Volume ẩn dependencies**: `/workspace/backend/vendor`, `/workspace/frontend/node_modules`
  - Tránh xung đột permission/OS giữa host và container.
  - Cài đặt phụ thuộc sẽ nằm trong volume của Docker (không xuất hiện trên host).
- **Named volumes dữ liệu**: `mysql-data`, `redis-data`
  - Dữ liệu còn giữ lại khi container bị xóa.
- **Bind config Nginx**: Ánh xạ file cấu hình để tùy biến reverse proxy mà không rebuild image.

### 5) Thứ tự thực thi khi mở Dev Container

1. Dev Containers đọc `devcontainer.json` → nhận biết compose file, service chính và danh sách `runServices`.
2. Tạo network `app-network` và volumes đặt tên nếu chưa tồn tại.
3. Build/pull images:
   - Build `laravel-app` từ `./docker/backend/Dockerfile`.
   - Build `nextjs-app` từ `../.devcontainer/docker/frontend/Dockerfile`.
   - Pull `nginx:alpine`, `mysql:8.0`, `redis/redis-stack:latest` nếu chưa có.
4. Khởi động `runServices` theo `depends_on`:
   - `db` và `redis` khởi động trước.
   - `laravel-app` khởi động kế tiếp (không chờ DB/Redis “ready”, chỉ chờ container started).
   - `nginx` phụ thuộc `laravel-app` nên khởi động sau đó.
   - `nextjs-app` khởi động song song theo danh sách.
5. Attach VS Code vào service chính `laravel-app` với `workspaceFolder=/workspace`.
6. Chạy `postCreateCommand` trong `laravel-app`:
   - `composer install` cài dependencies vào volume `/workspace/backend/vendor` (volume ẩn).
   - `php artisan key:generate` tạo `APP_KEY` cho Laravel.
7. Thiết lập `forwardPorts` để bạn truy cập các cổng từ host.

### 6) Kết quả sau từng bước (tóm tắt)

- Sau bước build/pull: Images sẵn sàng cho 5 dịch vụ.
- Sau khi network/volume tạo xong: `app-network`, `mysql-data`, `redis-data` tồn tại.
- Sau khi `db` chạy: MySQL nghe tại `db:3306` (trong network) và `localhost:3306` (trên host).
- Sau khi `redis` chạy: Redis nghe tại `redis:6379` và `localhost:6379`.
- Sau khi `laravel-app` chạy: Mã nguồn có ở `/workspace/backend`; Xdebug dùng `client_host=host.docker.internal` (trong WSL2 vẫn hợp lệ); vendor nằm trong volume.
- Sau khi `nginx` chạy: Truy cập Laravel qua `http://localhost:8080` (tùy nginx.conf).
- Sau khi `nextjs-app` chạy: Truy cập Next.js qua `http://localhost:3000`.
- Sau `postCreateCommand`: Laravel sẵn sàng với dependencies và `APP_KEY` hợp lệ.

### 7) Lưu ý môi trường (WSL2/Linux)

- `host.docker.internal` hoạt động trong Docker Desktop và WSL2 mới; nếu gặp vấn đề, cân nhắc chỉ định IP của host.
- `depends_on` không đợi dịch vụ “ready”; nếu cần, dùng healthchecks hoặc scripts đợi DB sẵn sàng trước migrations.
- Chạy lệnh trong container:
  - Laravel: `docker compose -f .devcontainer/docker-compose.yml exec laravel-app bash`
  - Next.js: `docker compose -f .devcontainer/docker-compose.yml exec nextjs-app sh`
  - MySQL CLI: `docker compose -f .devcontainer/docker-compose.yml exec db mysql -u$MYSQL_USER -p$MYSQL_PASSWORD $MYSQL_DATABASE`

### 8) Kiểm tra nhanh

- Laravel:
  - `php -v`, `php artisan --version`
  - `php artisan migrate` (sau khi DB sẵn sàng)
- Next.js:
  - `npm run dev` (nếu Dockerfile không tự chạy), truy cập `http://localhost:3000`
- Nginx + Laravel:
  - Truy cập `http://localhost:8080`
- MySQL/Redis từ host:
  - MySQL: `mysql -h 127.0.0.1 -P 3306 -u user -p`
  - Redis: `redis-cli -h 127.0.0.1 -p 6379 ping`

### 9) Tóm lược mounts/cổng theo dịch vụ

- `laravel-app`:
  - Mount: `..:/workspace`, volume ẩn `vendor`, `frontend/node_modules`
  - Ports (forward): 9000 (PHP-FPM/Xdebug)
- `nextjs-app`:
  - Mount: `..:/workspace`, volume ẩn `frontend/node_modules`
  - Ports: `3000:3000`
- `nginx`:
  - Mount: mã nguồn + `nginx.conf`
  - Ports: `8080:80`
- `db`:
  - Volume: `mysql-data:/var/lib/mysql`
  - Ports: `3306:3306`
- `redis`:
  - Volume: `redis-data:/data`
  - Ports: `6379:6379`

Tài liệu này bám sát cấu hình hiện tại. Nếu bạn thay đổi `nginx.conf`, Dockerfile, hay biến môi trường `.env`, hành vi có thể khác đi tương ứng.
