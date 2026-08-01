#!/usr/bin/env python3
import os
import zipfile

def build_zip():
    base_dir = os.path.dirname(os.path.dirname(os.path.abspath(__file__)))
    zip_path = os.path.join(base_dir, "simulador_deploy.zip")
    
    files_to_include = [
        "README_HOSTINGER.md",
        ".env.example",
    ]
    
    dirs_to_include = [
        "public_html",
        "private",
        "src",
        "database",
    ]
    
    print(f"Creando paquete de despliegue en: {zip_path}")
    with zipfile.ZipFile(zip_path, 'w', zipfile.ZIP_DEFLATED) as zipf:
        for f in files_to_include:
            full_f = os.path.join(base_dir, f)
            if os.path.exists(full_f):
                zipf.write(full_f, f)
                
        for d in dirs_to_include:
            full_d = os.path.join(base_dir, d)
            for root, dirs, files in os.walk(full_d):
                for file in files:
                    # Excluir archivos ocultos innecesarios o .git
                    if file.startswith('.DS_Store'):
                        continue
                    file_path = os.path.join(root, file)
                    arcname = os.path.relpath(file_path, base_dir)
                    zipf.write(file_path, arcname)
                    
    print("¡Paquete simulador_deploy.zip creado exitosamente!")

if __name__ == "__main__":
    build_zip()
