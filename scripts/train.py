import pandas as pd
from transformers import MarianMTModel, MarianTokenizer, Trainer, TrainingArguments
from datasets import load_dataset, Dataset
from pymongo import MongoClient

# Параметри з'єднання з MongoDB
client = MongoClient('mongodb://localhost:27017/')
db = client['docker_dataset']
collection = db['management']

# Завантаження даних з колекції (без змін)
data = pd.DataFrame(list(collection.find()))
data.drop('_id', axis=1, inplace=True)
dataset = Dataset.from_pandas(data)

# Підготовка датасету
def preprocess_data(examples):
    model_inputs = tokenizer(examples['input'], max_length=512, truncation=True, padding="max_length")
    labels = tokenizer(examples['output'], max_length=512, truncation=True, padding="max_length")
    model_inputs['labels'] = labels['input_ids']
    return model_inputs

# Завантаження моделі та токенізатора MarianMT
model_name = 'Helsinki-NLP/opus-mt-uk-en'
tokenizer = MarianTokenizer.from_pretrained(model_name)
model = MarianMTModel.from_pretrained(model_name)

tokenized_dataset = dataset.map(preprocess_data, batched=True)

# Розділення датасету на навчальний і тестовий
train_test_split = tokenized_dataset.train_test_split(test_size=0.1)
train_dataset = train_test_split['train']
test_dataset = train_test_split['test']

# Налаштування тренування
training_args = TrainingArguments(
    output_dir='./results',
    num_train_epochs=3,
    per_device_train_batch_size=4,
    per_device_eval_batch_size=4,
    warmup_steps=500,
    weight_decay=0.01,
    logging_dir='./logs',
    logging_steps=10,
)

trainer = Trainer(
    model=model,
    args=training_args,
    train_dataset=train_dataset,
    eval_dataset=test_dataset,
)

# Тренування моделі
trainer.train()

# Збереження моделі
model.save_pretrained('./docker_command_model')
tokenizer.save_pretrained('./docker_command_model')
