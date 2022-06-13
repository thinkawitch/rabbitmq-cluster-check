## The reports


### 1. One million simple messages in a row 
One sender and one receiver. Time measured on sender, sender has no output to console, no delays in loop. 
10_make_flood.php, 10_receive_flood.php.

| Send / receive                                                                       | Time, seconds | Messages, per seconds |
|--------------------------------------------------------------------------------------|---------------|-----------------------|
| the same node                                                                        | 18 - 21       | ~ 52600               |
| different nodes in cluster,  <br/>without queue replication                          | 25 - 28       | ~ 37735               |              
| with queue replication, <br/>no matter if sender/receiver connected to the same node | 57 - 78       | ~ 14814               |

Sender has output to console and delay in loop.  
12_reconnecting_sender.php, 12_reconnecting_receiver.php.

| Send / receive                                                                           | Time, seconds | Messages, per seconds |
|------------------------------------------------------------------------------------------|---------------|-----------------------|
| one node, no output, no delay                                                            | 22            | ~ 45450               |
| one node, no output, with delay                                                          | 200           | ~ 5000                |
| one node, with output, with delay                                                        | 197           | ~ 5070                |
| cluster with nodes, +output+delay <br/>switched off/on in process of sending & receiving | 182 - 196     | ~ 5290                |
| cluster with nodes, -output-delay <br/>switched off/on in process of sending & receiving | 26 - 32       | ~ 34400               |


raw data:  
22 23.5 22.7 21.7  
200.2 198.9 199.9  
196.9 197.03 195.8  
181.5 195.8  
31.8 25.8 25.7
