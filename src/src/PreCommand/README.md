# PreCommand Architecture System

## Overview

The PreCommand Architecture System is the foundational layer that handles configuration resolution, extension management, test package downloading, and command preprocessing before actual test execution. This system provides a clean separation between command input processing and execution, enabling complex configuration merging, validation, and resource resolution.

## Architecture

```mermaid
flowchart TD
    A[PreCommand System] --> B[Configuration Layer]
    A --> C[Extensions Layer]
    A --> D[Download Layer]
    A --> E[Objects Layer]

    B --> F[ConfigResolver]
    B --> G[QitJsonParser]
    B --> H[TestPackageManifestParser]

    C --> I[ExtensionResolver]
    C --> J[DependencyResolver]
    C --> K[ExtensionCacheManager]
    C --> L[ExtensionMetadataFetcher]
    C --> M[EntrypointDetector]

    D --> N[TestPackageDownloader]
    D --> O[EnvironmentDownloader]

    E --> P[Extension Object]
    E --> Q[TestPackage Object]
    E --> R[TestPackageManifest]
    E --> S[SutInput Object]

    F --> T[JSON Schema Validation]
    F --> U[Configuration Merging]
    F --> V[CLI Override Handling]

    I --> W[Source Resolution wporg wccom local url]
    I --> X[Cache-First Resolution]
    I --> Y[Metadata Fetching]

    N --> Z[Checksum-Based Caching]
    N --> AA[Rolling Version Support]
    N --> BB[Package Validation]
```

## Critical Logic Flows

### Complex Configuration Resolution Process
The configuration resolution involves a sophisticated multi-layer precedence system that can be confusing due to its complexity:

```mermaid
flowchart TD
    A[Configuration Request] --> B[File Discovery Phase]
    B --> C{Override Config Specified?}
    C -->|Yes| D[Use Override Config File]
    C -->|No| E[Working Directory Discovery]

    E --> F[Check qit.json]
    F --> G[Check .qit.json]
    G --> H[Check qit.yml]
    H --> I[Check .qit.yml]

    D --> J[Load Base Config]
    I --> J

    J --> K[Look for Override Files]
    K --> L[Load qit.override.json or yml]
    L --> M[Type Validation & Merging]

    M --> N{Validation Errors?}
    N -->|Yes| O[Report Configuration Errors]
    N -->|No| P[Normalize Keys]

    P --> Q[Handle Plural and Singular Forms]
    Q --> R[Final Merged Configuration]

    style B fill:#ffeb3b
    style M fill:#ff9800
    style Q fill:#ff5722
```

**Critical Points**:
1. **File Discovery Order**: 4 different file patterns checked in working directory
2. **Override Precedence**: Override files always win over base files
3. **Type Validation**: Strict type checking during merge prevents invalid configs
4. **Key Normalization**: Bidirectional conversion between plural/singular forms (plugins/plugin)

### Extension Handler Selection Decision Tree
Extension resolution uses a complex handler selection system with runtime class discovery:

```mermaid
flowchart TD
    A[Extension Input] --> B[Runtime Handler Discovery]
    B --> C[Scan Declared Classes]
    C --> D[Find Custom Handlers]
    D --> E[Test Handler Patterns]

    E --> F{Custom Handler Match?}
    F -->|Yes| G[Use Custom Handler]
    F -->|No| H[Fallback Handler Selection]

    H --> I{Local File Path?}
    I -->|Yes| J[FileHandler]

    I -->|No| K{URL Pattern?}
    K -->|Yes| L[URLHandler]

    K -->|No| M{WPORG Pattern?}
    M -->|Yes| N[QITHandler wporg source]

    M -->|No| O{WCCOM Pattern?}
    O -->|Yes| P[QITHandler wccom source]

    O -->|No| Q[Default QITHandler]

    style B fill:#ffeb3b
    style E fill:#ff9800
    style H fill:#ff5722
```

**Critical Points**:
1. **Runtime Discovery**: `get_declared_classes()` used to find custom handlers
2. **Pattern Matching**: Complex regex patterns determine handler selection
3. **Fallback Chain**: Multiple fallback levels prevent resolution failures
4. **Source Attribution**: Handler selection determines metadata source

## Core Components

### Configuration Layer

#### 1. ConfigResolver (`ConfigResolver.php`)
**Purpose**: Single-pass configuration resolver handling JSON loading, validation, extends resolution, and CLI overrides.

**Key Features**:
- **JSON Schema Validation**: Ensures configuration integrity
- **Extends Resolution**: Handles configuration inheritance
- **CLI Override Merging**: Command-line parameter precedence
- **Extension Creation**: Converts config to Extension objects
- **Single-Pass Processing**: Efficient configuration resolution

**Replaces Legacy Components**:
- ResolvedConfiguration
- ConfigMerger
- ExtensionFactory
- Parts of QitJsonParser

#### 2. QitJsonParser (`QitJsonParser.php`)
**Purpose**: Parses and validates qit.json configuration files.

**Key Features**:
- Configuration file discovery and loading
- Schema validation against qit-schema.json
- Environment variable substitution
- Profile and extends chain resolution

#### 3. TestPackageManifestParser (`TestPackageManifestParser.php`)
**Purpose**: Handles test package manifest parsing and validation.

**Key Features**:
- Test package manifest validation
- Package metadata extraction
- Dependency requirement parsing
- Compatibility checking

### Extensions Layer

#### 1. ExtensionResolver (`ExtensionResolver.php`)
**Purpose**: Main extension resolver that orchestrates the resolution process with intelligent caching.

**Key Features**:
- **Cache-First Resolution**: Checks cache before API calls
- **Source Resolution**: Handles wporg/wccom/local/url sources
- **API Call Minimization**: Prevents rate limiting
- **Shared Cache**: Cross-test-run cache coordination

**Caching Strategy**:
```mermaid
sequenceDiagram
    participant ER as ExtensionResolver
    participant Cache as ExtensionCache
    participant API as Metadata API
    participant Downloader as Downloader

    ER->>ER: Resolve source (wporg/wccom/local/url)
    ER->>Cache: Check if extension cached
    alt Extension in cache
        Cache->>ER: Return cached extension
    else Extension not in cache
        ER->>API: Fetch metadata (only if needed)
        API->>ER: Extension metadata
        ER->>Downloader: Download extension
        Downloader->>Cache: Store in cache
        Cache->>ER: Return extension
    end
```

#### 2. DependencyResolver (`DependencyResolver.php`)
**Purpose**: Resolves extension dependency trees and handles conflicts.

**Key Features**:
- Recursive dependency resolution
- Version constraint satisfaction
- Circular dependency detection
- Conflict resolution strategies

#### 3. ExtensionCacheManager (`ExtensionCacheManager.php`)
**Purpose**: Manages local caching of downloaded extensions.

**Key Features**:
- File-based extension caching
- Cache invalidation strategies
- Storage optimization
- Integrity verification

#### 4. ExtensionMetadataFetcher (`ExtensionMetadataFetcher.php`)
**Purpose**: Fetches extension metadata from various sources with caching.

**Key Features**:
- Multi-source metadata fetching (wporg, wccom)
- Metadata caching to reduce API calls
- Version resolution and compatibility checking
- Rate limiting protection

#### 5. EntrypointDetector (`EntrypointDetector.php`)
**Purpose**: Detects main plugin/theme files within extensions.

**Key Features**:
- Plugin header detection
- Theme style.css detection
- Main file identification
- Multi-file extension handling

### Download Layer

#### 1. TestPackageDownloader (`TestPackageDownloader.php`)
**Purpose**: Downloads and caches remote test packages with checksum-based validation.

**Key Features**:
- **Checksum-Based Caching**: SHA256 validation for integrity
- **Rolling Version Support**: Handles latest, rc, nightly versions
- **Cache Key Generation**: `test_package_checksum_[checksum]` format
- **Immutable + Mutable Support**: Works with both version types

**Caching Flow**:
```mermaid
sequenceDiagram
    participant TPD as TestPackageDownloader
    participant API as QIT API
    participant Cache as Local Cache
    participant Validator as Package Validator

    TPD->>API: Fetch package metadata (with checksum)
    API->>TPD: Package info + SHA256 checksum
    TPD->>Cache: Check cache for checksum
    alt Checksum in cache
        Cache->>TPD: Return cached package
    else Checksum not in cache or changed
        TPD->>API: Download package
        API->>TPD: Package archive
        TPD->>Validator: Validate package integrity
        Validator->>TPD: Validation results
        TPD->>Cache: Store with checksum key
        Cache->>TPD: Package ready
    end
```

#### 2. EnvironmentDownloader (`EnvironmentDownloader.php`)
**Purpose**: Downloads environment-specific resources and configurations.

**Key Features**:
- Environment resource fetching
- Configuration template downloading
- Resource caching and validation
- Environment compatibility checking

### Objects Layer

#### 1. Extension Object (`Extension.php`)
**Purpose**: Represents a resolved extension with metadata and file paths.

**Properties**:
- Extension metadata (name, version, type)
- File system paths
- Dependency information
- Activation requirements

#### 2. TestPackage Object (`TestPackage.php`)
**Purpose**: Represents a test package with execution requirements.

**Properties**:
- Package identification
- Execution configuration
- Dependency requirements
- Resource locations

#### 3. TestPackageManifest (`TestPackageManifest.php`)
**Purpose**: Comprehensive test package manifest with validation and metadata.

**Properties**:
- Package metadata
- Test configuration
- Dependency declarations
- Execution requirements

#### 4. SutInput Object (`SutInput.php`)
**Purpose**: Represents System Under Test input specification.

**Properties**:
- SUT identification
- Version requirements
- Configuration overrides
- Test parameters

## Logic Flow

### Configuration Resolution Flow
```mermaid
sequenceDiagram
    participant CLI as Command Line
    participant CR as ConfigResolver
    participant Parser as QitJsonParser
    participant Schema as JSON Schema
    participant Extensions as ExtensionResolver

    CLI->>CR: load(config_file, cli_overrides)
    CR->>Parser: Parse qit.json
    Parser->>CR: Raw configuration
    CR->>Schema: Validate against schema
    Schema->>CR: Validation results
    CR->>CR: Resolve extends chain
    CR->>CR: Merge CLI overrides
    CR->>Extensions: Create Extension objects
    Extensions->>CR: Resolved extensions
    CR->>CLI: Complete configuration
```

### Extension Resolution Flow
```mermaid
sequenceDiagram
    participant Config as Configuration
    participant ER as ExtensionResolver
    participant Cache as ExtensionCache
    participant Fetcher as MetadataFetcher
    participant DR as DependencyResolver

    Config->>ER: Resolve extensions list
    loop For each extension
        ER->>Cache: Check cache first
        alt Not in cache
            ER->>Fetcher: Fetch metadata
            Fetcher->>ER: Extension metadata
        end
        ER->>DR: Resolve dependencies
        DR->>ER: Dependency tree
        ER->>Cache: Ensure extension cached
    end
    ER->>Config: All extensions resolved
```

### Test Package Download Flow
```mermaid
sequenceDiagram
    participant Command as Command
    participant TPD as TestPackageDownloader
    participant API as QIT Registry
    participant Validator as ArtifactValidator
    participant Cache as Cache Manager

    Command->>TPD: Download test package
    TPD->>API: Get package metadata + checksum
    API->>TPD: Metadata with SHA256
    TPD->>Cache: Check checksum cache
    alt Cache hit
        Cache->>TPD: Return cached package
    else Cache miss or checksum changed
        TPD->>API: Download package archive
        API->>TPD: Package data
        TPD->>Validator: Validate package
        Validator->>TPD: Validation success
        TPD->>Cache: Store with checksum key
    end
    TPD->>Command: Package ready for use
```

## Advanced Features

### Configuration Inheritance
```mermaid
flowchart LR
    A[base-config.json] --> B[profile-config.json]
    B --> C[qit.json]
    C --> D[CLI Overrides]
    D --> E[Final Configuration]

    F[extends: base-config] --> A
    G[profile: my-profile] --> B
    H[--env VAR=value] --> D
```

### Caching Strategy
- **Extension Caching**: File-based with integrity checks
- **Metadata Caching**: In-memory with TTL expiration
- **Package Caching**: Checksum-based for version integrity
- **Shared Cache**: Cross-session cache reuse

### Schema Validation
- **qit-schema.json**: Main configuration schema
- **test-package-manifest-schema.json**: Test package validation
- **ctrf-schema.json**: Test result format validation

## Integration Points

### With Commands System
- **Pre-execution**: Configuration resolution before command execution
- **Override Handling**: CLI parameter precedence management
- **Resource Preparation**: Extension and package preparation

### With Environment System
- **Resource Provision**: Extensions and packages for environment setup
- **Configuration Injection**: Resolved config into environment
- **Dependency Management**: Extension dependency satisfaction

### With Test Package System
- **Package Resolution**: Remote package downloading and caching
- **Manifest Processing**: Test package configuration parsing
- **Dependency Linking**: Package-to-extension relationships

## Important Classes Summary

| Class | Primary Responsibility | Replaces/Consolidates |
|-------|------------------------|----------------------|
| `ConfigResolver` | Single-pass configuration resolution | ResolvedConfiguration, ConfigMerger, ExtensionFactory |
| `ExtensionResolver` | Cache-first extension resolution | Multiple resolver classes |
| `TestPackageDownloader` | Checksum-based package caching | Package download logic |
| `DependencyResolver` | Extension dependency trees | Dependency management |
| `ExtensionCacheManager` | Extension file caching | Cache coordination |

## Testing Strategy

PreCommand system should be tested for:
- **Configuration Resolution**: Schema validation and merging logic
- **Extension Resolution**: Source detection and caching
- **Package Downloading**: Checksum validation and integrity
- **Dependency Resolution**: Complex dependency trees
- **Cache Management**: Cache invalidation and integrity
- **Schema Compliance**: All validation rules

## Performance Optimizations

1. **Cache-First Resolution**: Minimize API calls
2. **Checksum Validation**: Prevent unnecessary downloads
3. **Single-Pass Processing**: Efficient configuration resolution
4. **Shared Caches**: Cross-session resource reuse
5. **Lazy Loading**: On-demand resource resolution

## Future Enhancements

1. **Distributed Caching**: Multi-machine cache sharing
2. **Advanced Dependency Resolution**: Complex constraint solving
3. **Package Signatures**: Cryptographic package verification
4. **Configuration Templates**: Reusable configuration patterns
5. **Performance Profiling**: Resolution time optimization