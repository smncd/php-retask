from retask import Queue

queue = Queue('php-and-python-example')
queue.connect()

while True:
    task = queue.dequeue()

    if task:
        print(task.data)
