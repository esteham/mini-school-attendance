FROM node:20-alpine

WORKDIR /usr/src/app

# Copy only lock / package files and run npm install
COPY package.json package-lock.json* yarn.lock* pnpm-lock.yaml* ./

RUN npm install

# In development we will bind-mount the code, so we are not copying src etc. here

EXPOSE 5173

CMD ["npm", "run", "dev", "--", "--host", "0.0.0.0", "--port", "5173"]
