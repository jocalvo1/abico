import subprocess
import pathlib
import tkinter as tk
import threading
import time
import webbrowser
from tkinter import messagebox

# Change this path if XAMPP is not installed in C:\xampp
XAMPP_DIR = pathlib.Path(r"C:\xampp")
EDGE_PATH = r"C:\Program Files (x86)\Microsoft\Edge\Application\msedge.exe"

def run(command):
    try:
        return subprocess.Popen(command, cwd=XAMPP_DIR, shell=True)
    except Exception as e:
        log_message(f"Error running {command}: {str(e)}", "red")
        return None

def log_message(message, color="green"):
    log.set(message)
    log_label.config(fg=color)
    root.update()

def start_services():
    try:
        log_message("Starting Apache...", "blue")
        apache = run("apache_start.bat")
        if not apache:
            return

        time.sleep(3)  # Give Apache time to start
        
        log_message("Starting MySQL...", "blue")
        mysql = run("mysql_start.bat")
        if not mysql:
            return

        log_message("✅ Apache and MySQL started successfully!", "green")
        
        # Open Microsoft Edge to the specified URL
        try:
            webbrowser.register('edge', None, webbrowser.BackgroundBrowser(EDGE_PATH))
            webbrowser.get('edge').open('http://localhost/abico/')
            log_message("Opening ABICO in Microsoft Edge...", "blue")
        except Exception as e:
            log_message(f"Error opening browser: {str(e)}", "red")
            messagebox.showerror("Browser Error", f"Could not open Microsoft Edge: {str(e)}")

    except Exception as e:
        log_message(f"Error: {str(e)}", "red")
        messagebox.showerror("Error", f"An error occurred: {str(e)}")

def start_all():
    threading.Thread(target=start_services, daemon=True).start()

# GUI setup
root = tk.Tk()
root.title("ABICO XAMPP Starter")

# Set window size and make it non-resizable
root.geometry("400x200")
root.resizable(False, False)

# Configure grid
root.columnconfigure(0, weight=1)
root.rowconfigure(1, weight=1)

# Create and place widgets
frame = tk.Frame(root, padx=20, pady=20)
frame.pack(expand=True, fill='both')

tk.Label(frame, text="ABICO Development Server", font=('Arial', 12, 'bold')).pack(pady=(0, 15))

start_button = tk.Button(
    frame,
    text="Start Services",
    command=start_all,
    width=20,
    height=2,
    bg="#4CAF50",
    fg="white",
    font=('Arial', 10, 'bold')
)
start_button.pack(pady=(0, 15))

log = tk.StringVar(value="Ready to start services...")
log_label = tk.Label(frame, textvariable=log, fg="green", wraplength=350, justify="center")
log_label.pack()

# Add some styling
root.configure(bg='#f0f0f0')
frame.configure(bg='#f0f0f0')

root.mainloop()
