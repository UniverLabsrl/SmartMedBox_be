import sys
import json
import joblib
import mysql.connector
import numpy as np
import os
import pandas as pd

# ===== CONFIG =====
DB_CONFIG = {
    'host': 'localhost',
    'user': 'smartmedbox',
    'password': '6cdshb9teDsmaHb',
    'database': 'smartmedbox'
}

BASE_DIR = os.path.dirname(os.path.abspath(__file__))
MODEL_DIR = os.path.abspath(os.path.join(BASE_DIR, '../modelli'))


SENSOR_MAP = {
    'Temperature': 'temp_eq',
    'Humidity': 'hum_eq',
    'cov1': 'cov1_eq',
    'cov2': 'cov2_eq',
    'cov3': 'cov3_eq',
    'cov4': 'cov4_eq',
    'cov5': 'cov5_eq',
    'Light': 'light_eq',
    'Vibration': 'vibration_eq'
}

FEATURES = list(SENSOR_MAP.values())

# ===== INPUT =====
data = json.loads(sys.stdin.read())
sensor_id = data['sensor_id']
product_type = data['product_type']

# ===== LOAD SENSOR DATA =====
conn = mysql.connector.connect(**DB_CONFIG)

query = """
SELECT type, AVG(value) as avg_value
FROM charts_data
WHERE sensor_id = %s
GROUP BY type
"""

df = pd.read_sql(query, conn, params=[sensor_id])
conn.close()

if df.empty:
    print(json.dumps({"error": "No sensor data found"}))
    sys.exit(1)

# ===== BUILD FEATURE VECTOR =====
features = {f: 0.0 for f in FEATURES}

for _, row in df.iterrows():
    sensor_type = row['type']
    if sensor_type in SENSOR_MAP:
        features[SENSOR_MAP[sensor_type]] = row['avg_value']

X = np.array([[features[f] for f in FEATURES]])

# ===== LOAD MODEL =====
model_path = os.path.join(MODEL_DIR, f"model_pt{product_type}.pkl")

if not os.path.exists(model_path):
    print(json.dumps({"error": "Model not found"}))
    sys.exit(1)

model = joblib.load(model_path)

prediction = model.predict(X)

print(json.dumps({
    "predicted_shelf_life": float(prediction[0]),
    "features_used": features
}))
