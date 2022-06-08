# RabbitMQ checks

### Set up the cluster

#### 1. erlang cookie for cluster
Make cfg file copy, update and set correct permissions.
```bash
cp ./docker-containers/rabbitmq/.erlang.cookie.dist ./docker-containers/rabbitmq/.erlang.cookie
sudo chmod 0600 ./config/.erlang.cookie
```

#### 2. enable http management
Enable plugins in ./docker-containers/rabbitmq/enabled_plugins file, or
activate with command line:
```bash
docker-compose exec rabbit1 bash
rabbitmq-plugins enable rabbitmq_management
restart docker
```

#### 3. enable cluster
Do for `rabbit2` and `rabbit3` containers.
```bash
docker-compose exec rabbit2 bash
rabbitmqctl stop_app
rabbitmqctl reset
rabbitmqctl join_cluster ${RABBIT_1_NODENAME}
rabbitmqctl start_app
```


Management web interface:  
http://${RABBIT_1_NODENAME}:15672/   
default user: guest / guest

#### 4. haproxy balancer
Make cfg file copy and update.
```bash
cp ./docker-containers/haproxy/haproxy.cfg.dist ./docker-containers/haproxy/haproxy.cfg
```
