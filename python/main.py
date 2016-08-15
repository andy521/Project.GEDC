import os
import redis
import pusher
from datetime import datetime

r = redis.StrictRedis(host=os.environ['CACHE_PORT_6379_TCP_ADDR'], port=os.environ['CACHE_PORT_6379_TCP_PORT'])

DATA_LENGTH = 30

pusher_client = pusher.Pusher(
    app_id='213316',
    key='24737bc4b88e96c1898c',
    secret='d6ab2f39949df87df8fa',
    ssl=True
)


def send_notification(_sensor_id):
    pusher_client.trigger('alert_channel', 'new_alert', {
        'sensorId': _sensor_id,
        'timestamp': datetime.now().strftime('%Y-%m-%d %H:%M:%S'),
        'type': 'fall'
    })

subscriber = r.pubsub()
subscriber.subscribe('motion')

for event in subscriber.listen():
    if event['type'] != 'message':
        continue
    try:
        sensor_id = int(event['data'])
        print 'Start calculating for device: {0}'.format(sensor_id)

        for i in range(0, DATA_LENGTH):
            element = str(r.lpop('motion:{0}'.format(sensor_id)))
            if element is not None:
                array = map(lambda x: int(x), element.split(','))
                print array
            # TODO: Do your calculation with the arrays
            # TODO: Don't forget to call #send_notification when you finished
    except ValueError, e:
        pass

