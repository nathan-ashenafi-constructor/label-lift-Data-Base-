import cv2
import numpy as np
import easyocr
import numpy as np

reader = easyocr.Reader(['en']) # this needs to run only once to load the model into memory

def extract_text(frame):
    
    result = reader.readtext( frame, detail=0)
    return result 


def videocapture() :
    cap= cv2.VideoCapture(0) #scan video using camera or get video from file if 0 is replaced with file 
    while True :
        ret, frame =cap.read() #read from the frame 

        if not ret :
            continue 

        frame = cv2.cvtColor(frame, cv2.COLOR_BGR2RGB)
        
        extracted_text= extract_text(frame)

        cv2.imshow("camera test", frame )
        if cv2.waitKey(1) & 0xFF == ord('q'): #use q key  to break the loop and stop recording 
            break

    cap.release()
    cv2.destroyAllWindows()
    return extracted_text

def main () :
    text=videocapture()
    print(text)

if __name__ == "__main__":
    main()


