from transformers import pipeline

# generator = pipeline('text-generation', model='distilgpt2')
generator = pipeline('text-generation')

# accepts a string prompt and returns generated text
# may take some time on first call due to model loading
def generate_text(prompt):
    return generator(prompt, num_return_sequences=1, truncation=True)[0]['generated_text']
    