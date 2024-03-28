import torch
from transformers import MarianMTModel, MarianTokenizer

# Завантаження збереженої моделі та токенізатора
model_path = './docker_command_model'
tokenizer = MarianTokenizer.from_pretrained(model_path)
model = MarianMTModel.from_pretrained(model_path)

# Функція для перекладу тексту
def translate(text, model, tokenizer):
    # Кодування тексту, який потрібно перекласти
    encoded_text = tokenizer(text, return_tensors="pt", padding=True, truncation=True, max_length=512)

    # Генерація перекладу використовуючи модель
    translated_tokens = model.generate(**encoded_text)

    # Декодування отриманих токенів у рядок
    translated_text = tokenizer.decode(translated_tokens[0], skip_special_tokens=True)
    return translated_text

# Тестовий приклад використання
input_text = "Запусти контейнер з образу alpine з назвою test"
translated_command = translate(input_text, model, tokenizer)

print(f"Вхідний текст: {input_text}")
print(f"Перекладена команда: {translated_command}")
