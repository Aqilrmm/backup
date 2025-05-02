import os
import json
import requests
from threading import Thread, Event
from tkinter import Tk, Label, Entry, Button, IntVar, filedialog, StringVar
from tkinter.ttk import Progressbar
import time

def get_file_size(url):
    r = requests.head(url)
    return int(r.headers.get('Content-Length', 0))

def supports_range(url):
    r = requests.head(url)
    return 'bytes' in r.headers.get('Accept-Ranges', '')

def load_meta(meta_file):
    if os.path.exists(meta_file):
        with open(meta_file, 'r') as f:
            return json.load(f)
    return {}

def save_meta(meta_file, data):
    with open(meta_file, 'w') as f:
        json.dump(data, f)

class DownloaderGUI:
    def __init__(self, master):
        self.master = master
        master.title("Python Downloader")

        Label(master, text="File URL:").grid(row=0, column=0, sticky='e')
        self.url_entry = Entry(master, width=50)
        self.url_entry.grid(row=0, column=1, columnspan=2, pady=5)

        Label(master, text="Save As:").grid(row=1, column=0, sticky='e')
        self.output_var = StringVar()
        self.output_entry = Entry(master, textvariable=self.output_var, width=38)
        self.output_entry.grid(row=1, column=1, pady=5)
        Button(master, text="Browse", command=self.browse_file).grid(row=1, column=2)

        Label(master, text="Threads:").grid(row=2, column=0, sticky='e')
        self.thread_count = IntVar(value=4)
        Entry(master, textvariable=self.thread_count, width=5).grid(row=2, column=1, sticky='w', pady=5)

        self.progress = Progressbar(master, length=400)
        self.progress.grid(row=3, column=0, columnspan=3, pady=10)

        self.status_label = Label(master, text="")
        self.status_label.grid(row=4, column=0, columnspan=3)

        Button(master, text="Start Download", command=self.start_download).grid(row=5, column=0, pady=10)
        Button(master, text="Pause", command=self.pause_download).grid(row=5, column=1)
        Button(master, text="Cancel", command=self.cancel_download).grid(row=5, column=2)

        self.pause_flag = Event()
        self.stop_flag = Event()

    def browse_file(self):
        file_path = filedialog.asksaveasfilename(defaultextension="")
        if file_path:
            self.output_var.set(file_path)

    def start_download(self):
        self.stop_flag.clear()
        self.pause_flag.clear()
        url = self.url_entry.get()
        filename = self.output_var.get()
        threads = self.thread_count.get()

        if not url or not filename or threads < 1:
            self.status_label.config(text="Please fill all fields correctly.")
            return

        Thread(target=self.resumeable_downloader, args=(url, filename, threads)).start()

    def pause_download(self):
        self.pause_flag.set()
        self.status_label.config(text="Download paused.")

    def cancel_download(self):
        self.stop_flag.set()
        url = self.url_entry.get()
        filename = self.output_var.get()
        if not filename:
            return
        for f in os.listdir():
            if f.startswith(filename) and (f.endswith(".meta") or ".part" in f):
                os.remove(f)
        self.status_label.config(text="Download canceled and cleaned.")

    def resumeable_downloader(self, url, filename, num_threads):
        self.status_label.config(text="Checking server...")

        if not supports_range(url):
            self.status_label.config(text="Server does not support resume.")
            return

        file_size = get_file_size(url)
        part_size = file_size // num_threads
        meta_file = filename + '.meta'
        meta = load_meta(meta_file)

        if not meta:
            for i in range(num_threads):
                start = i * part_size
                end = start + part_size - 1 if i < num_threads - 1 else file_size - 1
                meta[str(i)] = {'start': start, 'end': end, 'downloaded': 0}
            save_meta(meta_file, meta)

        self.progress["maximum"] = file_size
        self.progress["value"] = sum(part["downloaded"] for part in meta.values())
        self.status_label.config(text="Downloading...")

        def download_part(i):
            part = meta[str(i)]
            downloaded = part['downloaded']
            start = part['start'] + downloaded
            end = part['end']
            if start > end:
                return

            headers = {'Range': f'bytes={start}-{end}'}
            response = requests.get(url, headers=headers, stream=True)
            part_path = f"{filename}.part{i}"
            mode = 'ab' if os.path.exists(part_path) else 'wb'

            with open(part_path, mode) as f:
                for chunk in response.iter_content(chunk_size=8192):
                    if self.stop_flag.is_set():
                        return
                    while self.pause_flag.is_set():
                        time.sleep(0.2)
                    if chunk:
                        f.write(chunk)
                        meta[str(i)]['downloaded'] += len(chunk)
                        save_meta(meta_file, meta)
                        self.progress["value"] += len(chunk)

        threads = []
        for i in range(num_threads):
            t = Thread(target=download_part, args=(i,))
            t.start()
            threads.append(t)

        for t in threads:
            t.join()

        if self.stop_flag.is_set():
            return

        if all(meta[str(i)]['downloaded'] >= (meta[str(i)]['end'] - meta[str(i)]['start'] + 1) for i in range(num_threads)):
            with open(filename, 'wb') as final:
                for i in range(num_threads):
                    part_path = f"{filename}.part{i}"
                    with open(part_path, 'rb') as pf:
                        final.write(pf.read())
                    os.remove(part_path)
            os.remove(meta_file)
            self.status_label.config(text="Download completed!")
        else:
            self.status_label.config(text="Paused. Click start to resume.")

# Run GUI
root = Tk()
gui = DownloaderGUI(root)
root.mainloop()
