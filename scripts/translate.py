import sys
from transformers import MarianMTModel, MarianTokenizer

def translate(text, model_path):
    # Завантаження моделі та токенізатора з локального шляху
    tokenizer = MarianTokenizer.from_pretrained(model_path)
    model = MarianMTModel.from_pretrained(model_path)

    # Кодування тексту, який потрібно перекласти
    encoded_text = tokenizer(text, return_tensors="pt", padding=True, truncation=True, max_length=512)

    # Генерація перекладу використовуючи модель
    translated_tokens = model.generate(**encoded_text)

    # Декодування отриманих токенів у рядок
    translated_text = tokenizer.decode(translated_tokens[0], skip_special_tokens=True)
    return translated_text

if __name__ == "__main__":
    model_path = sys.argv[1]  # Шлях до моделі передається як перший аргумент командного рядка
    text_to_translate = sys.argv[2]  # Текст для перекладу передається як другий аргумент
    translated_text = translate(text_to_translate, model_path)
    print(translated_text)
