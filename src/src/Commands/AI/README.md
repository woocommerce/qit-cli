# AI Commands Module

## Overview

The AI Commands module provides intelligent context and tooling for Agentic AI systems (like Claude Code) to better understand, debug, and work with QIT CLI. This module bridges the gap between human debugging workflows and AI-assisted development.

## Architecture

```mermaid
flowchart TD
    A[AI Commands Entry Point] --> B[ContextCommand]
    A --> C[InstallAgentsCommand]

    B --> D[Context Types]
    D --> E[failed-e2e]
    D --> F[qit-basics]
    D --> G[understanding-test-packages]
    D --> H[writing-test-packages]
    D --> I[test-script-execution]

    C --> J[Claude Code Integration]
    J --> K[claude agents directory]
    J --> L[claude commands directory]
    J --> M[qit slash command]

    E --> N[Failure Analysis]
    N --> O[Logs & Debugging Steps]

    F --> P[Architecture Documentation]
    P --> Q[Orchestration Model]

    G --> R[Test Package Lifecycle]
    R --> S[Global Setup and Teardown]
```

## Core Components

### 1. ContextCommand (`ContextCommand.php`)
**Purpose**: Provides specialized context for different QIT operations to help AI systems understand and debug issues.

**Key Features**:
- **failed-e2e**: Investigation context for debugging E2E test failures
- **qit-basics**: QIT's orchestration model and architecture explanations
- **understanding-test-packages**: Test package lifecycle and state management
- **writing-test-packages**: Best practices for test package creation
- **test-script-execution**: How test package scripts execute in QIT

**Usage Pattern**:
```bash
qit ai:context failed-e2e [run_id]
qit ai:context qit-basics
qit ai:context understanding-test-packages
```

### 2. InstallAgentsCommand (`InstallAgentsCommand.php`)
**Purpose**: Installs QIT-specific AI agent configurations for Claude Code integration.

**Key Features**:
- Installs agents to `~/.claude/agents/`
- Creates `/qit` slash command for Claude Code
- Provides specialized QIT workflow understanding
- Enables contextual debugging assistance

**Installation Locations**:
- `~/.claude/agents/` - AI agent configurations
- `~/.claude/commands/` - Slash command definitions

## Logic Flow

### Context Provision Flow
```mermaid
sequenceDiagram
    participant User
    participant ContextCommand
    participant QIT_System
    participant AI_Agent

    User->>ContextCommand: qit ai:context failed-e2e
    ContextCommand->>QIT_System: Fetch failure details
    ContextCommand->>QIT_System: Gather logs & artifacts
    ContextCommand->>AI_Agent: Provide structured context
    AI_Agent->>User: Intelligent debugging assistance
```

### Agent Installation Flow
```mermaid
sequenceDiagram
    participant User
    participant InstallAgentsCommand
    participant FileSystem
    participant Claude_Code

    User->>InstallAgentsCommand: qit ai:install_agents
    InstallAgentsCommand->>FileSystem: Write agent configs to ~/.claude/agents/
    InstallAgentsCommand->>FileSystem: Install /qit command to ~/.claude/commands/
    InstallAgentsCommand->>User: Installation complete
    Note over Claude_Code: Restart required for changes
    User->>Claude_Code: /qit help
    Claude_Code->>User: QIT-aware assistance
```

## Integration Points

### With QIT Core System
- **Config System**: Accesses QIT configuration for context
- **Logging System**: Retrieves failure logs and debugging information
- **Test Results**: Analyzes test execution outcomes
- **Environment Data**: Provides environment context for debugging

### With External AI Systems
- **Claude Code**: Primary integration target
- **Agent Definitions**: Structured context for AI understanding
- **Slash Commands**: Direct AI interaction patterns
- **Context Types**: Specialized knowledge domains

## Important Classes

| Class | Responsibility | Key Methods |
|-------|----------------|-------------|
| `ContextCommand` | Context provision for AI systems | `execute()`, context type handlers |
| `InstallAgentsCommand` | Claude Code agent installation | `execute()`, file system operations |

## Context Types Detail

### failed-e2e
- **Purpose**: Debug E2E test failures
- **Data Provided**: Failure logs, commands run, environment state
- **Use Case**: AI-assisted failure analysis and resolution

### qit-basics
- **Purpose**: Explain QIT's unique orchestration approach
- **Data Provided**: Architecture overview, key concepts
- **Use Case**: Onboarding AI agents to QIT's design philosophy

### understanding-test-packages
- **Purpose**: Test package lifecycle education
- **Data Provided**: Global setup, setup, teardown phases
- **Use Case**: Help AI understand test package state management

## Future Enhancements

1. **writing-test-packages**: Best practices context (planned)
2. **Performance Context**: Performance test debugging assistance
3. **Configuration Context**: Complex configuration debugging
4. **Extension Context**: Plugin/theme specific debugging assistance

## Dependencies

- `QIT_CLI\Config` - Configuration access
- `QIT_CLI\QITInput` - Input handling
- `Symfony\Component\Console` - Command infrastructure

## Testing Strategy

AI commands should be tested for:
- Context accuracy and completeness
- Agent installation integrity
- Claude Code integration functionality
- Context type coverage and usefulness