from flask import Flask, request, send_file, jsonify
from flask_cors import CORS
import pyttsx3
import tempfile
import os
import threading

app = Flask(__name__)
CORS(app)

lock = threading.Lock()

@app.route("/speak", methods=["POST"])
def speak():
    data = request.get_json()
    text = data.get("text", "")
    speed = data.get("speed", "normal")
    voice = data.get("voice", "female")

    if not text:
        return jsonify({"error": "No text provided"}), 400

    with lock:
        try:
            # Configure pyttsx3 engine
            engine = pyttsx3.init()
            voices = engine.getProperty('voices')

            # Set voice based on gender hint
            selected_voice = None
            for v in voices:
                if voice.lower() == "male" and ("male" in v.name.lower() or "male" in v.id.lower()):
                    selected_voice = v
                    break
                elif voice.lower() == "female" and ("female" in v.name.lower() or "female" in v.id.lower()):
                    selected_voice = v
                    break

            # Fallback to default if not found
            if not selected_voice:
                selected_voice = voices[0] if voice.lower() == "male" else (voices[1] if len(voices) > 1 else voices[0])

            engine.setProperty('voice', selected_voice.id)

            # Adjust rate
            if speed == "slow":
                engine.setProperty('rate', 125)
            elif speed == "fast":
                engine.setProperty('rate', 200)
            else:
                engine.setProperty('rate', 160)

            # Save audio to temp file
            tmp_file = tempfile.NamedTemporaryFile(delete=False, suffix=".mp3")
            tmp_filename = tmp_file.name
            tmp_file.close()

            engine.save_to_file(text, tmp_filename)
            engine.runAndWait()

            return send_file(tmp_filename, mimetype="audio/mpeg", as_attachment=False)

        except Exception as e:
            return jsonify({"error": "Failed to generate audio", "details": str(e)}), 500

if __name__ == "__main__":
    app.run(debug=True)