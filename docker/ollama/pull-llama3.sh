#!/bin/bash

/bin/ollama serve &
# Record Process ID.
pid=$!

# Pause for Ollama to start.
sleep 5

echo "🔴==================== Retrieve LLAMA3 model"
ollama pull llama3
echo "🟢==================== LLAMA3 model pulled"

echo "🔴==================== Retrieve embedding model"
ollama pull mxbai-embed-large
echo "🟢==================== embedding model pulled"

# Wait for Ollama process to finish.
wait $pid