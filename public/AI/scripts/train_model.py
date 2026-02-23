import pandas as pd
import joblib
import os
import mysql.connector
from sklearn.ensemble import RandomForestRegressor
from sklearn.model_selection import train_test_split
from sklearn.metrics import mean_squared_error

# ===== CONFIG =====
DB_CONFIG = {
    'host': '127.0.0.1',
    'user': 'root',
    'password': '',
    'database': 'smartmedbox'
}

MODEL_DIR = 'modelli'
os.makedirs(MODEL_DIR, exist_ok=True)

FEATURES = [
    'temp_eq', 'hum_eq',
    'cov1_eq', 'cov2_eq', 'cov3_eq', 'cov4_eq', 'cov5_eq',
    'light_eq', 'vibration_eq'
]

TARGET = 'actual_result'

# ===== LOAD DATA =====
conn = mysql.connector.connect(**DB_CONFIG)
query = "SELECT * FROM shelf_life_training_data WHERE actual_result IS NOT NULL"
df = pd.read_sql(query, conn)
conn.close()

if df.empty:
    print("❌ Nessun dato disponibile.")
    exit()

# ===== TRAIN PER PRODUCT TYPE =====
for product_type in df['product_type'].unique():

    subset = df[df['product_type'] == product_type]

    if len(subset) < 10:
        print(f"⚠️ product_type {product_type}: dati insufficienti ({len(subset)})")
        continue

    X = subset[FEATURES]
    y = subset[TARGET]

    X_train, X_test, y_train, y_test = train_test_split(
        X, y, test_size=0.2, random_state=42
    )

    model = RandomForestRegressor(
        n_estimators=200,
        max_depth=None,
        random_state=42,
        n_jobs=-1
    )

    model.fit(X_train, y_train)

    y_pred = model.predict(X_test)
    mse = mean_squared_error(y_test, y_pred)

    print(f"✅ product_type={product_type} | MSE={mse:.2f}")

    model_path = os.path.join(MODEL_DIR, f"model_pt{product_type}.pkl")
    joblib.dump(model, model_path)

    print(f"💾 Modello salvato: {model_path}")
