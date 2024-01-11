PHP and Python
==============

This example shows how you can use differnet languages on each end of the queue; the producer is written in PHP and the worker in Python.

To run this demo:

```bash
# Create Python venv, fetch dependencies and run worker.
bash run-worker.sh

# Run producer.
php producer.php
```