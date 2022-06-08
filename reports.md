## The reports


### 1. One million simple messages in a row 
One sender and one receiver.

| Send / receive                                                                  | Time, seconds | Messages, per seconds |
|---------------------------------------------------------------------------------|---------------|-----------------------|
| the same node                                                                   | 18 - 20       | ~ 52600               |
| different nodes in cluster,  <br/>without queue replication                     | 25 - 28       | ~ 37735               |              
| with queue replication, <br/>no matter if sender/receiver connected to the same node | 57 - 78       | ~ 14814               |

