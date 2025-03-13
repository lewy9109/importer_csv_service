# Project: Docker-based Load Balanced PHP Application

## Overview

This project is a Docker based PHP application using Nginx as a load balancer with a Round Robin strategy. It includes two PHP servers and background workers managed by Supervisor.

## Purpose

The purpose of this application is to process a CSV file containing 1 million rows and store the data asynchronously in the database using RabbitMQ. To prevent race conditions during the write operations, database transactions are utilized.

## Architecture

- **Load Balancer:** Nginx (Round Robin strategy)
- **Application Servers:** 2 PHP servers
- **Workers:**
    - `report-worker` (2 processes)
    - `user-worker` (10 processes)
- **Process Management:** Supervisor

## Technologies Used

- **Docker & Docker Compose**
- **Nginx** (Load Balancer)
- **PHP**
- **MariaDB** (Database)
- **RabbitMQ** (Message Broker)
- **Redis** (Cache)
- **Symfony Messenger** (for background workers)
- **Supervisor** (process control)

## Setup

### Prerequisites

- Docker & Docker Compose installed

### Installation

1. Clone the repository:
   ```bash
   git clonegit@github.com:lewy9109/importer_csv_service.git
   cd importer_csv_servic
   ```
2. Build and start the containers:
   ```bash
    ./run.sh 
   ```

### Configuration

Ensure the environment variables are set correctly in your `.env` file.

### Load Balancer Configuration

Nginx is set up to distribute requests between the two PHP servers using a Round Robin strategy.

### Supervisor Workers

The application includes two sets of background workers:

#### Report Worker

```ini
[program:report-worker]
command=php /var/www/html/bin/console messenger:consume report_process --memory-limit=256M --limit=100 --time-limit=3600 --sleep=0.1
process_name=report-worker-%(process_num)s
numprocs=2
autostart=true
autorestart=true
startretries=3
stderr_logfile=/var/log/supervisor/report_worker.err.log
stdout_logfile=/var/log/supervisor/report_worker.out.log
stopwaitsecs=1
```

#### User Worker

```ini
[program:user-worker]
command=php /var/www/html/bin/console messenger:consume user_process --memory-limit=256M --limit=100 --time-limit=3600 --sleep=0.1
process_name=user-worker-%(process_num)s
numprocs=10
autostart=true
autorestart=true
startretries=3
stderr_logfile=/var/log/supervisor/user_worker.err.log
stdout_logfile=/var/log/supervisor/user_worker.out.log
stopwaitsecs=1
```

## Asynchronous Processing Time

The estimated processing time for an asynchronous CSV file with the following structure:

```
ID    Full name            E-mail            City
1     Pauline Alexander    ceev@eva.ch       Cihkapa
2     Ethan Garner        hulsabwozev.th    Panzikvus
... to 1 mln rows
```

is approximately **~ 3 min**.

## Usage

To check the logs:

```bash
docker-compose logs -f
```


## Contributing

Feel free to submit issues and pull requests!

## License

This project is licensed under the MIT License.

