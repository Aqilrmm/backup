import sys
import subprocess
from PyQt6.QtWidgets import (
    QApplication, QWidget, QVBoxLayout, QLabel, QPushButton,
    QHBoxLayout, QTextEdit, QScrollArea, QFrame, QSpacerItem, QSizePolicy, QLineEdit
)
from PyQt6.QtCore import Qt
from PyQt6.QtGui import QPixmap, QFont

apps = [
    {"name": "GIMP", "package": "gimp", "description": "Professional image editor with open-source freedom.", "icon": "🖌️"},
    {"name": "VLC", "package": "vlc", "description": "Versatile media player for all formats.", "icon": "📺"},
    {"name": "LibreOffice", "package": "libreoffice", "description": "Modern, powerful, and free office suite.", "icon": "📄"},
    {"name": "Audacity", "package": "audacity", "description": "High-quality audio editor and recorder.", "icon": "🎙️"},
    {"name": "Inkscape", "package": "inkscape", "description": "Professional vector design and illustration tool.", "icon": "🎨"}
]

class UltraModernAppStore(QWidget):
    def __init__(self):
        super().__init__()
        self.setWindowTitle("Devuna App Store")
        self.resize(900, 700)
        self.setStyleSheet("""
            QWidget {
                background-color: #f0f2f5;
                font-family: 'Segoe UI', sans-serif;
            }
            QLabel#Title {
                font-size: 30px;
                font-weight: 700;
                color: #1e1e2f;
            }
            QLabel#Subtitle {
                font-size: 15px;
                color: #666;
                margin-bottom: 20px;
            }
            QFrame#Card {
                background-color: white;
                border-radius: 16px;
                padding: 20px;
                margin-bottom: 18px;
                border: 1px solid #ddd;
            }
            QLabel#AppName {
                font-size: 18px;
                font-weight: bold;
                color: #333;
            }
            QLabel#AppDesc {
                font-size: 14px;
                color: #666;
            }
            QPushButton {
                background-color: #007bff;
                color: white;
                padding: 8px 22px;
                border-radius: 10px;
                font-weight: bold;
            }
            QPushButton:hover {
                background-color: #0056d2;
            }
            QLineEdit {
                padding: 10px;
                border-radius: 12px;
                border: 1px solid #ccc;
                font-size: 14px;
                margin-bottom: 16px;
            }
            QTextEdit {
                background-color: #1e1e2f;
                color: #dcdcdc;
                padding: 10px;
                font-family: monospace;
                border-radius: 10px;
            }
        """)

        layout = QVBoxLayout(self)
        layout.setContentsMargins(40, 20, 40, 20)

        title = QLabel("🚀 Devuna App Store")
        title.setObjectName("Title")
        subtitle = QLabel("A curated experience to install apps with one-click.")
        subtitle.setObjectName("Subtitle")

        self.search_input = QLineEdit()
        self.search_input.setPlaceholderText("🔍 Search for apps...")
        self.search_input.textChanged.connect(self.update_displayed_apps)

        layout.addWidget(title)
        layout.addWidget(subtitle)
        layout.addWidget(self.search_input)

        self.scroll_area = QScrollArea()
        self.scroll_area.setWidgetResizable(True)
        self.content_widget = QWidget()
        self.content_layout = QVBoxLayout(self.content_widget)

        self.scroll_area.setWidget(self.content_widget)
        layout.addWidget(self.scroll_area, 1)

        self.output = QTextEdit()
        self.output.setReadOnly(True)
        self.output.setMinimumHeight(180)
        layout.addWidget(self.output)

        self.displayed_cards = []
        self.update_displayed_apps()

    def update_displayed_apps(self):
        query = self.search_input.text().lower()
        for card in self.displayed_cards:
            card.setParent(None)
        self.displayed_cards.clear()

        for app in apps:
            if query in app["name"].lower() or query in app["description"].lower():
                card = self.create_app_card(app)
                self.content_layout.addWidget(card)
                self.displayed_cards.append(card)

    def create_app_card(self, app):
        card = QFrame()
        card.setObjectName("Card")
        layout = QHBoxLayout(card)

        icon_label = QLabel(app.get("icon", "📦"))
        icon_label.setFont(QFont("Arial", 24))

        text_layout = QVBoxLayout()
        name_label = QLabel(app["name"])
        name_label.setObjectName("AppName")
        desc_label = QLabel(app["description"])
        desc_label.setWordWrap(True)
        desc_label.setObjectName("AppDesc")

        text_layout.addWidget(name_label)
        text_layout.addWidget(desc_label)

        install_btn = QPushButton("Install")
        install_btn.clicked.connect(lambda: self.install_app(app["package"]))

        layout.addWidget(icon_label)
        layout.addLayout(text_layout)
        layout.addStretch()
        layout.addWidget(install_btn)

        return card

    def install_app(self, package):
        self.output.append(f"\n🔧 Installing {package}...\n")
        try:
            process = subprocess.Popen(
                ["pkexec", "apt", "install", package, "-y"],
                stdout=subprocess.PIPE,
                stderr=subprocess.STDOUT,
                text=True
            )
            for line in process.stdout:
                self.output.append(line.strip())
        except Exception as e:
            self.output.append(f"❌ Error: {str(e)}")


if __name__ == "__main__":
    app = QApplication(sys.argv)
    window = UltraModernAppStore()
    window.show()
    sys.exit(app.exec())
