FROM grafana/k6:latest

# Install additional dependencies if needed
RUN apk add --no-cache curl

# Set working directory
WORKDIR /tests

# Copy QIT helpers
COPY qitHelpers.js /qitHelpers/qitHelpers.js

# Default command
CMD ["run", "--help"]