import argparse
import numpy as np
import pandas as pd
import joblib
import sys
from pathlib import Path

# ======= Configuración base =======
FEATURES = [
    "PersonalityScore",
    "SkillScore",
    "InterviewScore",
    "EducationLevel",
    "ExperienceYears",
    "RecruitmentStrategy",
]

DEFAULT_MODEL = "modelo_rf_contratacion.pkl"
DEFAULT_CANDIDATOS = "../datasets/nuevos_candidatos.csv"

def detectar_id(df: pd.DataFrame) -> str | None:
    candidatos = ["CandidateID", "candidate_id", "ID", "Id", "id", "Nombre", "Name"]
    for c in candidatos:
        if c in df.columns:
            return c
    return None

def cargar_modelo(ruta_modelo: str):
    try:
        return joblib.load(ruta_modelo)
    except Exception as e:
        print(f"[ERROR] No se pudo cargar el modelo desde '{ruta_modelo}': {e}")
        sys.exit(1)

def cargar_candidatos(ruta_csv: str) -> pd.DataFrame:
    try:
        df = pd.read_csv(ruta_csv)
    except Exception as e:
        print(f"[ERROR] No se pudo leer el CSV de candidatos '{ruta_csv}': {e}")
        sys.exit(1)

    faltantes = [c for c in FEATURES if c not in df.columns]
    if faltantes:
        print(f"[ERROR] Faltan columnas requeridas en el CSV: {faltantes}")
        sys.exit(1)

    df[FEATURES] = df[FEATURES].apply(pd.to_numeric, errors="coerce")
    return df

def evaluar(modelo, df_cand: pd.DataFrame, threshold: float = 0.80) -> pd.DataFrame:
    X = df_cand[FEATURES]
    proba = modelo.predict_proba(X)[:, 1]  # prob. de clase 1
    score_pct = proba * 100.0
    decision = np.where(proba >= threshold, "APTO", "NO APTO")

    out = df_cand.copy()
    id_col = detectar_id(out)
    if id_col is None:
        out["Etiqueta"] = [f"Candidato #{i+1}" for i in range(len(out))]
    else:
        out["Etiqueta"] = out[id_col].astype(str)

    out["score_pct"] = np.round(score_pct, 2)
    out["decision"] = decision
    out = out.sort_values(by="score_pct", ascending=False).reset_index(drop=True)
    out["rank"] = out.index + 1
    return out

def imprimir_en_grupos(df_result: pd.DataFrame, group_size: int = 5):
    for i in range(0, len(df_result), group_size):
        chunk = df_result.iloc[i:i+group_size]
        gnum = i // group_size + 1
        print(f"\n========== Grupo {gnum} ({i+1} - {i+len(chunk)}) ==========")
        for _, r in chunk.iterrows():
            print(f"[{int(r['rank']):>3}] {r['Etiqueta']:<25} "
                  f"→ {r['score_pct']:6.2f}%  |  {r['decision']}")

def main():
    parser = argparse.ArgumentParser(description="Evaluación de nuevos candidatos.")
    parser.add_argument("--modelo", "-m", default=DEFAULT_MODEL, help="Archivo del modelo (pkl)")
    parser.add_argument("--candidatos", "-c", default=DEFAULT_CANDIDATOS, help="CSV de candidatos")
    parser.add_argument("--umbral", "-t", type=float, default=0.8, help="Umbral de aptitud")
    parser.add_argument("--grupo", "-g", type=int, default=5, help="Tamaño de grupo al imprimir")
    parser.add_argument("--salida", "-o", default=None, help="CSV de salida")

    args = parser.parse_args()

    modelo = cargar_modelo(args.modelo)
    df_cand = cargar_candidatos(args.candidatos)
    resultados = evaluar(modelo, df_cand, threshold=args.umbral)

    print("\n===== RESUMEN DE EVALUACIÓN =====")
    print(f"Candidatos evaluados: {len(resultados)}")
    print(f"Umbral (APTO si prob >= umbral): {args.umbral:.2f}")
    aptos = (resultados['decision'] == "APTO").sum()
    print(f"APTO: {aptos}  |  NO APTO: {len(resultados)-aptos}")

    imprimir_en_grupos(resultados, group_size=args.grupo)

    if args.salida is None:
        base = Path(args.candidatos)
        args.salida = str(base.with_name(base.stem + "_evaluados.csv"))
    resultados.to_csv(args.salida, index=False)
    print(f"\nResultados guardados en: {args.salida}")

if __name__ == "__main__":
    main()
