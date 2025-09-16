# Environment Management System

## Overview

The Environment Management System handles Docker-based test environments, environment variables, package orchestration, and test execution lifecycle. This system provides isolated, reproducible environments for running WordPress tests with different configurations, extensions, and test packages.

## Architecture

```mermaid
flowchart TD
    A[Environment Management] --> B[EnvironmentManager]
    A --> C[PackageOrchestrator]
    A --> D[Docker Manager]
    A --> E[Environment Types]

    B --> F[Environment Variables]
    B --> G[Secret Management]
    B --> H[Variable Distribution]
    F --> I[CLI Variables --env]
    F --> J[Environment Files --env_file]
    G --> K[Secret Validation]
    G --> L[Redaction Logic]
    H --> M[Host Context]
    H --> N[Docker Context]
    H --> O[Node.js Context]

    C --> P[Test Execution UI]
    C --> Q[Lifecycle Management]
    C --> R[CTRF Validation]
    P --> S[Package Status Display]
    P --> T[Progress Tracking]
    Q --> U[Global Setup]
    Q --> V[Test Execution]
    Q --> W[Global Teardown]

    D --> X[Container Management]
    D --> Y[Volume Mapping]
    D --> Z[Network Configuration]

    E --> AA[E2E Environment]
    E --> BB[Performance Environment]
    E --> CC[Custom Environments]

    AA --> DD[E2EEnvInfo]
    AA --> EE[E2EEnvironment]
    BB --> FF[PerformanceEnvInfo]
    BB --> GG[PerformanceEnvironment]
```

## Critical Logic Flows

### Complex Environment Setup with Option Precedence
The environment setup process involves a sophisticated three-layer precedence system that manually parses command-line arguments to determine user intent:

```mermaid
flowchart TD
    A[Environment Setup Request] --> B[Parse GLOBALS argv]
    B --> C[Detect User-Provided Options]
    C --> D[Normalize Option Shortcuts]

    D --> E{Option Explicitly Set?}
    E -->|Yes| F[Add to Override Layer]
    E -->|No| G[Add to Default Layer]

    F --> H[Runtime Options Layer]
    G --> I[Default Values Layer]

    H --> J[Configuration Loading]
    I --> J
    J --> K[Config File Layer]

    K --> L[Three-Layer Precedence Resolution]
    L --> M[Runtime > Config > Defaults]
    M --> N[Final Environment Configuration]

    O[Manual argv Parsing] --> C
    P[Option Shortcut Resolution] --> D

    style B fill:#ffeb3b
    style L fill:#ff9800
    style M fill:#ff5722
```

**Critical Points**:
1. **Manual argv Parsing**: Uses `$GLOBALS['argv']` instead of Symfony Console mechanisms
2. **User Intent Detection**: Complex logic to determine if user explicitly set options vs defaults
3. **Three-Layer Precedence**: Runtime options > Config file > Defaults
4. **Shortcut Normalization**: Option shortcuts resolved before precedence application

### WooCommerce Version Resolution with Test Tag Preservation
Version resolution involves complex state preservation and conditional transformations:

```mermaid
flowchart TD
    A[WooCommerce Version Request] --> B[Extract Existing WooCommerce Config]
    B --> C[Preserve Test Tags & Actions]
    C --> D{Version Type?}

    D -->|Nightly| E[Use nightly build]
    D -->|RC| F[Use release candidate]
    D -->|Stable/Version| G[Use specific version]
    D -->|URL| H[Use custom URL]
    D -->|GitHub| I[Use GitHub tag]

    E --> J[Apply Preserved Config]
    F --> J
    G --> J
    H --> J
    I --> J

    J --> K{Test Tags String?}
    K -->|Yes| L[Convert to Array]
    K -->|No| M[Keep as Array]

    L --> N[Final WooCommerce Config]
    M --> N

    style C fill:#ffeb3b
    style J fill:#ff9800
    style K fill:#ff5722
```

**Critical Points**:
1. **State Preservation**: Existing test_tags and actions must be preserved across version changes
2. **Conditional Conversion**: String test_tags converted to arrays only when needed
3. **Multiple Version Sources**: Different resolution paths for nightly, RC, stable, URL, GitHub
4. **Configuration Mutation**: Method mutates based on existing plugin configurations

## Core Components

### 1. EnvironmentManager (`EnvironmentManager.php`)
**Purpose**: Centralized management of environment variables, secrets, and secure distribution.

**Key Features**:
- **Environment Variable Parsing**: CLI `--env` and `--env_file` processing
- **Secret Management**: Secure handling of sensitive values
- **Variable Distribution**: Context-aware variable injection
- **Redaction Logic**: Automatic secret filtering from logs
- **Immutable State**: Prevents configuration drift

**Design Principles**:
- Single source of truth for all environment variables
- Immutable after initialization to prevent state bugs
- Clear separation between regular variables and secrets
- Secure by default with automatic secret redaction

### 2. PackageOrchestrator (`PackageOrchestrator.php`)
**Purpose**: Orchestrates test package execution with rich UI and lifecycle management.

**Key Features**:
- **Beautiful UI**: Native Symfony Console components for status display
- **Lifecycle Management**: Global setup, test execution, teardown phases
- **CTRF Validation**: Common Test Result Format validation
- **Progress Tracking**: Real-time package execution status
- **CI Environment Detection**: Optimized output for CI/CD systems

### 3. Docker Manager (`Docker.php`)
**Purpose**: Manages Docker containers, networks, and volumes for test environments.

**Key Features**:
- **Container Lifecycle**: Start, stop, monitor Docker containers
- **Volume Management**: Host-to-container file system mapping
- **Network Configuration**: Isolated test networks
- **Resource Management**: Memory, CPU constraints
- **Health Checking**: Container readiness verification

### 4. Environment Types

#### E2E Environment (`E2EEnvironment.php`, `E2EEnvInfo.php`)
**Purpose**: End-to-end testing environment with WordPress, WooCommerce, and test packages.

**Features**:
- WordPress environment setup
- WooCommerce installation and configuration
- Plugin/theme activation
- Test package execution
- Browser automation support

#### Performance Environment
**Purpose**: Performance testing with K6 and metrics collection.

**Features**:
- K6 test runner integration
- Performance metrics collection
- Load testing capabilities
- Baseline comparison

## Logic Flow

### Environment Setup Flow
```mermaid
sequenceDiagram
    participant User
    participant EnvManager as EnvironmentManager
    participant Docker as Docker Manager
    participant Orchestrator as PackageOrchestrator

    User->>EnvManager: qit env:up --env VAR=value
    EnvManager->>EnvManager: Parse environment variables
    EnvManager->>EnvManager: Validate required secrets
    EnvManager->>EnvManager: Store variables securely
    EnvManager->>Docker: Create Docker environment
    Docker->>Docker: Start containers
    Docker->>Docker: Configure networks
    Docker->>Docker: Mount volumes
    Docker->>Orchestrator: Environment ready
    Orchestrator->>User: Environment available
```

### Test Package Execution Flow
```mermaid
sequenceDiagram
    participant User
    participant Orchestrator as PackageOrchestrator
    participant EnvManager as EnvironmentManager
    participant Docker as Docker Manager
    participant TestRunner as Test Runner

    User->>Orchestrator: Execute test packages
    Orchestrator->>Orchestrator: Initialize UI sections
    Orchestrator->>EnvManager: Get environment context
    EnvManager->>Orchestrator: Sanitized environment vars

    loop For each test package
        Orchestrator->>Orchestrator: Update package status
        Orchestrator->>Docker: Run global setup
        Docker->>TestRunner: Execute setup scripts
        TestRunner->>Docker: Setup complete
        Orchestrator->>Docker: Run test package
        Docker->>TestRunner: Execute test specs
        TestRunner->>Docker: Test results
        Orchestrator->>Docker: Run global teardown
        Docker->>TestRunner: Execute teardown scripts
        TestRunner->>Docker: Teardown complete
        Orchestrator->>Orchestrator: Collect results
    end

    Orchestrator->>Orchestrator: Validate CTRF output
    Orchestrator->>User: Final results display
```

### Environment Variable Distribution Flow
```mermaid
sequenceDiagram
    participant CLI
    participant EnvManager as EnvironmentManager
    participant Host
    participant Docker
    participant NodeJS as Node.js

    CLI->>EnvManager: --env KEY=value --env_file .env
    EnvManager->>EnvManager: Parse variables and files
    EnvManager->>EnvManager: Identify secrets
    EnvManager->>EnvManager: Validate required variables

    EnvManager->>Host: Distribute host variables
    EnvManager->>Docker: Distribute Docker variables
    EnvManager->>NodeJS: Distribute Node.js variables

    Note over EnvManager: All secrets automatically redacted from logs
```

## Important Classes & Responsibilities

| Class | Responsibility | Key Methods |
|-------|----------------|-------------|
| `EnvironmentManager` | Environment variable management | `parseEnvVars()`, `validateSecrets()`, `distributeVars()` |
| `PackageOrchestrator` | Test execution orchestration | `execute()`, `updateStatus()`, `collectResults()` |
| `Docker` | Container management | `startContainer()`, `configureNetwork()`, `mountVolumes()` |
| `E2EEnvironment` | E2E test environment | `setup()`, `teardown()`, `executeTests()` |
| `CTRFValidator` | Test result validation | `validate()`, `formatResults()` |

## Environment Variable System

### Variable Types
1. **Regular Variables**: Standard environment variables
2. **Secrets**: Sensitive values (API keys, passwords, tokens)
3. **File Variables**: Variables loaded from `.env` files
4. **CLI Variables**: Variables passed via `--env` flag

### Distribution Contexts
1. **Host Context**: Variables available to QIT CLI process
2. **Docker Context**: Variables injected into containers
3. **Node.js Context**: Variables for test package execution

### Security Features
- **Automatic Redaction**: Secrets never appear in logs
- **Validation**: Required secrets verified before execution
- **Immutable State**: Variables cannot be changed after initialization
- **Context Separation**: Different contexts get appropriate variable subsets

## Test Package Lifecycle

### Phase 1: Global Setup
- Environment initialization
- Extension installation
- Database setup
- Service configuration

### Phase 2: Test Execution
- Test package loading
- Spec file execution
- Result collection
- Status monitoring

### Phase 3: Global Teardown
- Result aggregation
- Resource cleanup
- Container shutdown
- Artifact collection

## Docker Integration

### Container Management
- **Lifecycle**: Start, monitor, stop containers
- **Health Checks**: Verify container readiness
- **Resource Limits**: CPU, memory constraints
- **Network Isolation**: Separate test networks

### Volume Management
- **Source Code**: Host-to-container file mapping
- **Test Artifacts**: Result collection volumes
- **Database Persistence**: Data volume management
- **Cache Optimization**: Shared dependency volumes

### Network Configuration
- **Service Discovery**: Container-to-container communication
- **Port Mapping**: External service access
- **Traffic Isolation**: Separate test networks
- **Load Balancing**: Multiple container coordination

## Advanced Features

### CTRF Integration
- **Result Validation**: Common Test Result Format validation
- **Result Merging**: Multiple package result aggregation
- **Format Conversion**: Convert between result formats
- **Metadata Enrichment**: Add environment context to results

### UI/UX Features
- **Real-time Status**: Live package execution updates
- **Progress Bars**: Visual execution progress
- **Color Coding**: Status-based output styling
- **CI Optimization**: Streamlined output for automation

### Performance Optimization
- **Container Reuse**: Warm container startup
- **Volume Caching**: Dependency caching
- **Parallel Execution**: Concurrent package execution
- **Resource Monitoring**: Memory and CPU tracking

## Integration Points

### With PreCommand System
- **Configuration**: Environment settings from config files
- **Extension Management**: Plugin/theme installation
- **Variable Resolution**: CLI override handling

### With Test Package System
- **Package Loading**: Test package discovery and loading
- **Execution Context**: Environment preparation for packages
- **Result Collection**: Package output aggregation

### With Commands System
- **Environment Commands**: `env:up`, `env:down`, `env:reset`
- **Test Commands**: Environment integration for test execution
- **Debug Commands**: Environment inspection and debugging

## Testing Strategy

Environment system should be tested for:
- **Variable Management**: Secure handling of environment variables
- **Docker Integration**: Container lifecycle management
- **Test Execution**: Package orchestration functionality
- **UI Components**: Status display and progress tracking
- **Security**: Secret redaction and secure distribution
- **Performance**: Resource usage and optimization

## Future Enhancements

1. **Multi-Environment Support**: Parallel environment execution
2. **Cloud Integration**: Cloud-based environment provisioning
3. **Advanced Monitoring**: Resource usage dashboards
4. **Environment Templates**: Pre-configured environment types
5. **Auto-scaling**: Dynamic resource allocation based on load