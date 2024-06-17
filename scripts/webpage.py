import sys
from bs4 import BeautifulSoup

def read_html(file_path):
    try:
        with open(file_path, 'r', encoding='utf-8') as file:
            content = file.read()
            return content
    except FileNotFoundError:
        print(f"Error: File {file_path} not found.")
        return None

if __name__ == "__main__":
    if len(sys.argv) != 2:
        print("Usage: python read_html.py <file_path>")
        sys.exit(1)

    file_path = sys.argv[1]
    html_content = read_html(file_path)

    if html_content:
        soup = BeautifulSoup(html_content, 'html.parser')
        print(soup.get_text())
