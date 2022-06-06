# RabbitMQ checks

### Set up the cluster

#### 1. erlang cookie for cluster
Edit and set correct permissions.
```bash
cp ./config/.erlang.cookie.dist ./config/.erlang.cookie
sudo chmod 0600 ./config/.erlang.cookie
```

#### 2. enable http management
Enable plugins in ./config/enabled_plugins file, or
activate with command line:
```bash
docker-compose exec rabbit1 bash
rabbitmq-plugins enable rabbitmq_management
restart docker
```

#### 3. enable cluster
```bash
docker-compose exec rabbit2 bash
rabbitmqctl stop_app
rabbitmqctl reset
rabbitmqctl join_cluster ${RABBIT_1_NODENAME}
rabbitmqctl start_app
```
```bash
docker-compose exec rabbit3 bash
rabbitmqctl stop_app
rabbitmqctl reset
rabbitmqctl join_cluster ${RABBIT_1_NODENAME}
rabbitmqctl start_app
```


Management web interface:  
http://${RABBIT_1_NODENAME}:15672/   
**default user**: guest / guest
