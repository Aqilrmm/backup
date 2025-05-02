from llama_index.llms.ollama import Ollama
llm = Ollama(model="gemma2")
llm.complete("Why is the sky blue?")
