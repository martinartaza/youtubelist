# Unit Tests for Artaza_YoutubeList Module

This directory contains unit tests for the Artaza_YoutubeList module.

## Structure

```
Test/Unit/
├── Helper/
│   └── DataTest.php
├── Block/
│   └── ProductPageTest.php
├── Setup/Patch/Data/
│   └── AddYoutubelistProductAttributeTest.php
├── phpunit.xml
└── README.md
```

## Running Tests

### Run all unit tests for the module:
```bash
vendor/bin/phpunit app/code/Artaza/YoutubeList/Test/Unit/
```

### Run specific test class:
```bash
vendor/bin/phpunit app/code/Artaza/YoutubeList/Test/Unit/Helper/DataTest.php
```

### Run with coverage report:
```bash
vendor/bin/phpunit --coverage-html coverage app/code/Artaza/YoutubeList/Test/Unit/
```

## Test Coverage

The unit tests cover:

1. **Helper\Data**: Tests for all methods in the Data helper class
   - getYouKey()
   - getUrlProduct()
   - isList()
   - isOnly()
   - isEmbed()
   - getTypeUrl()
   - getArrayVideos()

2. **Block\ProductPage**: Tests for the ProductPage block
   - getVideos()

3. **Setup\Patch\Data\AddYoutubelistProductAttribute**: Tests for the setup patch
   - apply()
   - revert()
   - getAliases()
   - getDependencies()

## Test Data

The tests use mock objects to simulate:
- YouTube API responses
- Product data
- Configuration values
- Database operations

## Best Practices

- Each test method tests a single functionality
- Tests are isolated and don't depend on each other
- Mock objects are used to avoid external dependencies
- Test names are descriptive and follow the pattern `testMethodNameWithScenario` 